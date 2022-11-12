<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Repositories;

use MediaUploader;
use App\Models\WebForm;
use Illuminate\Support\Arr;
use App\Enums\WebFormSection;
use App\Mail\WebFormSubmitted;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Fields\Field;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\WebFormRequest;
use App\Support\WebForm\FormSubmission;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Repository\AppRepository;
use App\Support\WebForm\FormSubmissionLogger;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\SourceRepository;
use App\Innoclapps\Criteria\WithTrashedCriteria;
use App\Contracts\Repositories\WebFormRepository;
use Plank\Mediable\Exceptions\MediaUploadException;

class WebFormRepositoryEloquent extends AppRepository implements WebFormRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return WebForm::class;
    }

    /**
     * Find web form by given uuid
     *
     * @param string $uuid
     *
     * @return \App\Models\WebForm|null
     */
    public function findByUuid(string $uuid) : ?WebForm
    {
        return $this->findWhere(['uuid' => $uuid])->first();
    }

    /**
     * Get the form by uuid prepared for display
     *
     * @param string $uuid
     *
     * @return \App\\Models\WebForm|null
     */
    public function findByUuidForDisplay(string $uuid) : ?WebForm
    {
        $form = $this->findByUuid($uuid);

        if ($form) {
            // Change the locale in case the fields are using the translation
            // function so the data can be properly shown
            // @todo, check this, perhaps not needed?
            \App::setLocale($form->locale);
            $this->addFieldToFieldSections($form);
        }

        return $form;
    }

    /**
     * Process the web form submission
     *
     * @param \App\Http\Requests\WebFormRequest $request
     *
     * @return void
     */
    public function processSubmission(WebFormRequest $request)
    {
        ChangeLogger::disable();
        $webForm = $request->webForm();

        $request->state = 'creating';
        $request->setResource('contacts');
        $resource = $request->resource();

        $firstNameField = $request->fields()->find('first_name');
        $displayName    = null;

        $phoneField = $request->fields()->find('phones');
        $emailField = $request->fields()->find('email');

        User::setAssigneer($webForm->creator);

        if (! $firstNameField) {
            $displayName = $emailField ?
            $request[$emailField->requestAttribute()] :
            $request[$phoneField->requestAttribute()][0]['number'] ?? 'Unknown';
        }

        $contact = $this->findDuplicateContact($request, $emailField, $phoneField);

        if ($contact) {
            // Track updated fields
            ChangeLogger::enable();
            $resource->setModel($contact);
            $resource->resourcefulHandler($request)->update($contact->id);
            $resource->setModel(null);
            ChangeLogger::disable();
        } else {
            $firstNameAttribute = $firstNameField ? $firstNameField->requestAttribute() : 'first_name';
            $firstName          = ! $firstNameField ? $displayName : $request[$firstNameField->requestAttribute()];

            $request->merge([
                $firstNameAttribute => $firstName,
                'user_id'           => $webForm->user_id,
                'source_id'         => $this->getSource()->getKey(),
            ]);

            $contact = $request->resource()->resourcefulHandler($request)->store();
        }

        $this->handleResourceUploadedFiles($request, $contact);

        // Update the displayable fallback name with the actual contact display name
        // for example, if the form had first name and lastname fields, the full name will be used as fallback
        $displayName = $contact->display_name;

        // Handle the deal creation
        $deal = $this->handleDealFields($request, $displayName, $contact);

        $request->setResource('companies');

        if ($request->fields()->isNotEmpty() ||
            collect($request->webForm()->fileSections())->where('resourceName', 'companies')->isNotEmpty()) {
            $company = $this->handleCompanyFields($request, $displayName, $deal, $contact);
            $this->handleResourceUploadedFiles($request, $company);
        }

        $changelog = (new FormSubmissionLogger(array_filter([
            'contacts'  => $contact,
            'deals'     => $deal,
            'companies' => $company ?? null,
        ]), $request))->log();

        $webForm->increment('total_submissions');

        $this->handleWebFormNotifications($webForm, $changelog);

        ChangeLogger::enable();
    }

    /**
     * Find duplicate contact
     *
     * @param \App\Http\Requests\WebFormRequest $request
     * @param \App\Innoclapps\Fields\Field|null $emailField
     * @param \App\Innoclapps\Fields\Field|null $phoneField
     *
     * @return \App\Models\Contact|null
     */
    protected function findDuplicateContact(WebFormRequest $request, ?Field $emailField, ?Field $phoneField)
    {
        $repository = $request->resource()->repository();
        $contact    = null;

        if ($emailField && ! empty($email = $request[$emailField->requestAttribute()])) {
            $contact = $repository->findByEmail($email);
        }

        if (! $contact && $phoneField && ! empty($phones = $request[$phoneField->requestAttribute()])) {
            foreach ($phones as $phone) {
                if ($contact = $repository->findByPhone($phone['number'])) {
                    break;
                }
            }
        }

        return $contact;
    }

    /**
     * Handle the web form deal fields
     *
     * @param \App\Http\Requests\WebFormRequest $request
     * @param string $fallbackName
     * @param \App\Models\Contact $contact
     *
     * @return \App\Models\Deal
     */
    protected function handleDealFields(WebFormRequest $request, $fallbackName, $contact)
    {
        $request->setResource('deals');

        $dealNameField = $request->fields()->find('name');

        $dealName = $dealNameField ?
            $dealNameField->attributeFromRequest(
                $request,
                $dealNameField->requestAttribute()
            ) :
            $fallbackName . ' Deal';

        if (! empty($request->webForm()->title_prefix)) {
            $dealName = $request->webForm()->title_prefix . $dealName;
        }

        $nameAttribute = $dealNameField ? $dealNameField->requestAttribute() : 'name';

        $request->merge([
            $nameAttribute => $dealName,
            'pipeline_id'  => $request->webForm()->submit_data['pipeline_id'],
            'stage_id'     => $request->webForm()->submit_data['stage_id'],
            'user_id'      => $request->webForm()->user_id,
            'web_form_id'  => $request->webForm()->id,
        ]);

        return tap(
            $request->resource()->resourcefulHandler($request)->store(),
            function ($deal) use ($request, $contact) {
                $request->resource()->repository()->attach($deal->id, 'contacts', $contact->id);
                $this->handleResourceUploadedFiles($request, $deal);
            }
        );
    }

    /**
     * Handle the company fields
     *
     * @param \App\Http\Requests\WebFormRequest $request
     * @param string $fallbackName
     * @param \App\Models\Deal $deal
     * @param \App\Models\Contact $contact
     *
     * @return \App\Models\Company|null
     */
    protected function handleCompanyFields(WebFormRequest $request, $fallbackName, $deal, $contact)
    {
        $fields            = $request->fields();
        $resource          = $request->resource();
        $companies         = $resource->repository();
        $companyNameField  = $fields->find('name');
        $companyEmailField = $fields->find('email');
        $companyEmail      = null;
        $companyName       = null;

        if ($companyEmailField) {
            $companyEmail = $request[$companyEmailField->requestAttribute()];
        }

        $companyName = ! $companyNameField ?
        $fallbackName . ' Company' :
        $request[$companyNameField->requestAttribute()];


        if (($companyEmail && $company = $companies->findByEmail($companyEmail)) ||
                $company = $companies->findByField('name', $companyName)->first()) {
            $resource->setModel($company);
            $resource->resourcefulHandler($request)->update($company->id);
            $resource->setModel(null);

            // It can be possible the contact to be already attached to the company e.q. in case the same form
            // is submitted twice, in this case, the company will exists as well the contact
            $resource->repository()->syncWithoutDetaching($company->id, 'contacts', $contact->id);
        } else {
            $nameAttribute = $companyNameField ? $companyNameField->requestAttribute() : 'name';
            $request->merge([
                $nameAttribute => $companyName,
                'user_id'      => $request->webForm()->user_id,
                'source_id'    => $this->getSource()->getKey(),
            ]);

            $company = $resource->resourcefulHandler($request)->store();

            $resource->repository()->attach($company->id, 'contacts', $contact->id);
        }

        $resource->repository()->attach($company->id, 'deals', $deal->id);

        return $company;
    }

    /**
     * Handle the resource uploaded files
     *
     * @param \App\Http\Requests\WebFormRequest $request
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    protected function handleResourceUploadedFiles($request, $model)
    {
        // Before this function is called, the resource must be set e.q. $request->setResource('companies');
        // so the sections are properly determined

        $files = collect(
            $request->webForm()->fileSections()
        )->where('resourceName', $request->resource()->name());

        if ($files->isNotEmpty()) {
            $files->each(function ($section) use ($request, $model) {
                foreach (Arr::wrap($request[$section['requestAttribute']]) as $uploadedFile) {
                    // try {
                    $media = MediaUploader::fromSource($uploadedFile)
                        ->toDirectory($model->getMediaDirectory())
                        ->upload();
                    // } catch (MediaUploadException $e) {
                    // $exception = $this->transformMediaUploadException($e);
                    /*
                                return $this->response(
                                    ['message' => $exception->getMessage()],
                                    $exception->getStatusCode()
                                );*/
                    //  }
                    $model->attachMedia($media, $model->getMediaTags());
                }
            });
        }
    }

    /**
     * Get the web form source
     *
     * @return \App\Models\Source
     */
    protected function getSource()
    {
        return app(SourceRepository::class)->findByFlag('web-form');
    }

    /**
     * Handle the web form notification
     *
     * @param \App\Models\WebForm $form
     * @param \App\Innoclapps\Models\Changelog $changelog
     *
     * @return void
     */
    protected function handleWebFormNotifications($form, $changelog)
    {
        if (count($form->notifications) === 0) {
            return;
        }

        foreach ($this->getNotificationRecipients($form) as $recipient) {
            Mail::to($recipient)->send(
                new WebFormSubmitted($form, new FormSubmission($changelog))
            );
        }
    }

    /**
     * Get the notification recipients
     *
     * @param \App\\Models\WebForm $form
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getNotificationRecipients($form)
    {
        $repository  = resolve(UserRepository::class);
        $users       = $repository->findWhereIn('email', $form->notifications)->toBase();
        $usersEmails = $users->pluck('email')->all();

        if ($usersEmails != $form->notifications) {
            $nonUsersEmails = array_diff($form->notifications, $usersEmails);
        }

        return $users->merge($nonUsersEmails ?? []);
    }

    /**
     * Add the form section field
     *
     * @param \App\Models\WebForm $form
     */
    protected function addFieldToFieldSections($form)
    {
        $sections = [];

        foreach ($form->sections as $key => $section) {
            switch ($section['type']) {
                case WebFormSection::FIELD->value:

                $sections[$key] = array_merge($section, [
                    'field' => $form->fieldByResource(
                        $section['attribute'],
                        $section['resourceName']
                    ),
                ]);

                break;
                default:
                $sections[$key] = $section;

                break;
            }
        }

        $form->setAttribute('sections', $sections);
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model) {
            resolve(DealRepository::class)
                ->pushCriteria(WithTrashedCriteria::class)
                ->massUpdate(['web_form_id' => null], ['web_form_id' => $model->getKey()]);
        });
    }

    /**
     * The relations that are required for the responsee
     *
     * @return array
     */
    protected function eagerLoad()
    {
        return [
            'user',
        ];
    }
}
