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

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use App\Contracts\Repositories\WebFormRepository;
use App\Innoclapps\Resources\Http\CreateResourceRequest;

class WebFormRequest extends CreateResourceRequest
{
    /**
     * Original input for the request before any modifications
     *
     * @var array
     */
    protected $originalInput = [];

    /**
     * @var \App\Models\WebForm
     */
    protected $webForm;

    /**
     * Request state
     *
     * @var string validating|creating
     */
    public $state = 'validating';

    /**
     * Get the web form for the request
     *
     * @return \App\Models\WebForm
     */
    public function webForm()
    {
        if ($this->webForm) {
            return $this->webForm;
        }

        $webForm = resolve(WebFormRepository::class)->findByUuid($this->uuid());

        return tap($this->webForm = $webForm, function ($instance) {
            abort_if(! Auth::check() && (is_null($instance) || ! $instance->isActive()), 404);
        });
    }

    /**
     * Get the form uuid
     */
    public function uuid()
    {
        return $this->route('uuid');
    }

    /**
     * Set the resource
     *
     * @param string $resourceName
     */
    public function setResource($resourceName)
    {
        $this->resource = $this->findResource($resourceName);

        $this->replaceInputForCurrentResource();

        return $this;
    }

    /**
     * Replace the request input for the current resource
     *
     * @return void
     */
    protected function replaceInputForCurrentResource()
    {
        // When changing resource, the actual input shoud be replaced from the actual resource
        // available fields/files to avoid any conflicts when saving the records
        // e.q. a company may have name, as well deal may have name
        // when using in WebFormRepositoryEloquent ->replace method, there may be conflicts

        /** @var array */
        $input = collect($this->webForm()->fileSections())->reduce(function ($input, $section) { // merge with initial
            $input[$section['requestAttribute']] = $this->originalInput[$section['requestAttribute']];

            return $input;
        }, $this->fields()->reduce(function ($input, $field) { // initial
            $input[$field->requestAttribute] = $this->originalInput[$field->requestAttribute];

            return $input;
        }, []));

        $this->replace($input);
    }

    /**
     * Get the resource for the request
     *
     * @return \App\Innoclapps\Resources\Resource
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Get the available resources based on the form sections with fields
     *
     * @return array
     */
    public function resources()
    {
        return $this->fields()->unique(function ($field) {
            return $field->meta()['resourceName'];
        })->map(fn ($field) => $field->meta()['resourceName'])->values()->all();
    }

    /**
     * Get the resource authorized fields for the request
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function authorizedFields()
    {
        return $this->fields()->filter->authorizedToSee();
    }

    /**
     * Get all the available fields for the request
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function allFields()
    {
        return $this->webForm()->fields();
    }

    /**
     * Get the web form fields
     *
     * @return \App\Innoclapps\Fields\FieldCollection
     */
    public function fields()
    {
        if ($this->state === 'validating') {
            return $this->allFields();
        }

        return $this->allFields()->filter(function ($field) {
            return $field->meta()['resourceName'] === $this->resource->name();
        });
    }

    /**
     * Get the files sections that are required
     *
     * @return array
     */
    protected function requiredFileSections()
    {
        return collect($this->webForm()->fileSections())->where('isRequired', true)->all();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge(parent::messages(), collect($this->requiredFileSections())
            ->mapWithKeys(function ($section) {
                return [$section['requestAttribute'] . '.required' => __('validation.required_file')];
            })->all());
    }

    /**
     * Get the error messages that are defined from the resource class
     *
     * @return void
     */
    public function messagesFromResource()
    {
        return [];
    }

    /**
     * Prepare the request for validation
     *
     * @return void
     */
    public function prepareForValidation()
    {
        \App::setLocale($this->webForm()->locale);

        parent::prepareForValidation();

        $this->setOriginalInput();
    }

    /**
     * Set the request original input
     *
     * @return void
     */
    public function setOriginalInput()
    {
        $this->originalInput = $this->all();

        return $this;
    }

    /**
     * Get the request original input
     *
     * @return array
     */
    public function getOriginalInput()
    {
        return $this->originalInput;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return with($this->allFields()->mapWithKeys(
            fn ($field) => $field->getCreationRules()
        )->all(), function ($rules) {
            if ($this->privacyPolicyAcceptIsRequired()) {
                $rules['_privacy-policy'] = 'accepted';
            }

            return $this->addFileSectionValidationRules($rules);
        });
    }

    /**
     * Add validation for the file sections
     *
     * @param array $rules
     */
    protected function addFileSectionValidationRules($rules)
    {
        foreach ($this->requiredFileSections() as $section) {
            $attribute = $section['requestAttribute'];

            $rules[$attribute] = ['required'];

            if ($section['multiple']) {
                $rules[$attribute][] = 'array';
            }

            $rules[$attribute . ($section['multiple'] ? '.*' : '')][] = 'max:' . config('mediable.max_size');
            $rules[$attribute . ($section['multiple'] ? '.*' : '')][] = 'mimes:' . implode(',', config('mediable.allowed_extensions'));
        }

        return $rules;
    }

    /**
     * Indicates whether the privacy policy must be accepted
     *
     * @return boolean
     */
    protected function privacyPolicyAcceptIsRequired()
    {
        return $this->webForm()->submitSection()['privacyPolicyAcceptIsRequired'] ?? false;
    }
}
