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

use App\Models\Contact;
use App\Enums\PhoneType;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Repository\SoftDeletes;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CallRepository;
use App\Contracts\Repositories\NoteRepository;
use App\Contracts\Repositories\CompanyRepository;
use App\Contracts\Repositories\ContactRepository;

class ContactRepositoryEloquent extends AppRepository implements ContactRepository
{
    use SoftDeletes;

    /**
     * Searchable fields
     *
     * Searches by firstname lastname too via SearchByFirstNameAndLastNameCriteria
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'email' => 'like',
        'phones.number',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Contact::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::restoring(function ($model) {
            $model->logToAssociatedRelationsThatRelatedInstanceIsRestored(['companies', 'deals']);
        });

        static::deleting(function ($model, $repository) {
            if ($model->isForceDeleting()) {
                $repository->purge($model);
            } else {
                $model->logToAssociatedRelationsThatRelatedInstanceIsTrashed(['companies', 'deals'], [
                     'key'   => 'contact.timeline.associate_trashed',
                     'attrs' => ['contactName' => $model->display_name],
                 ]);

                $model->guests()->delete();
            }
        });
    }

    /**
     * Purge the contact data
     *
     * @param \App\Models\Contact $contact
     *
     * @return void
     */
    protected function purge($contact)
    {
        foreach (['companies', 'emails', 'deals', 'activities'] as $relation) {
            tap($contact->{$relation}(), function ($query) {
                if ($query->getModel()->usesSoftDeletes()) {
                    $query->withTrashed();
                }

                $query->detach();
            });
        }

        $contact->guests()->forceDelete();

        $contact->loadMissing(['notes', 'calls']);
        resolve(NoteRepository::class)->delete($contact->notes);
        resolve(CallRepository::class)->delete($contact->calls);
    }

    /**
     * Find contact by email address
     *
     * @param string $email
     *
     * @return \App\Models\Contact|null
     */
    public function findByEmail(string $email) : ?Contact
    {
        return $this->findByField('email', $email)->first();
    }

    /**
     * Find contact by the given phone number
     *
     * @param string $phone
     * @param \App\Enums\PhoneType|null $type
     *
     * @return \App\Models\Contact|null
     */
    public function findByPhone(string $phone, ?PhoneType $type = null)
    {
        return $this->whereHas('phones', function ($query) use ($phone, $type) {
            if ($type) {
                $query->where('type', $type);
            }

            return $query->where('number', $phone);
        })->all()->first();
    }

    /**
     * Associate companies to a given contact by email domain
     *
     * @param \App\Models\Contact $contact
     *
     * @return void
     */
    public function associateCompaniesByEmailDomain(Contact $contact)
    {
        $companyRepository = resolve(CompanyRepository::class);

        $emailDomain = substr($contact->email, strpos($contact->email, '@') + 1);
        $companies   = $companyRepository->findByDomain($emailDomain);

        ChangeLogger::asSystem();

        $contact->companies()->syncWithoutDetaching($companies);

        ChangeLogger::asSystem(false);
    }

    /**
     * The relations that are required for the responsee
     *
     * @return array
     */
    protected function eagerLoad()
    {
        $this->withCount(['calls', 'notes']);

        return [
                'media',
                'changelog',
                'changelog.pinnedTimelineSubjects',
                'companies.phones', // for calling
                'deals.stage', 'deals.pipeline', 'deals.pipeline.stages' => function ($query) {
                    return $query->orderBy('display_order');
                },
            ];
    }
}
