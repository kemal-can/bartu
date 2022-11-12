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

use App\Models\Company;
use App\Innoclapps\Repository\SoftDeletes;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\CallRepository;
use App\Contracts\Repositories\NoteRepository;
use App\Contracts\Repositories\CompanyRepository;

class CompanyRepositoryEloquent extends AppRepository implements CompanyRepository
{
    use SoftDeletes;

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'name'  => 'like',
        'email' => 'like',
        'domain',
        'phones.number',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Company::class;
    }

    /**
     * Get companies by domain name
     *
     * @param string $domain
     *
     * @return mixed
     */
    public function findByDomain(string $domain)
    {
        return $this->findByField('domain', $domain);
    }

    /**
     * Find company by email address
     *
     * @param string $email
     *
     * @return \App\Models\Company|null
     */
    public function findByEmail(string $email) : ?Company
    {
        return $this->findByField('email', $email)->first();
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::restoring(function ($model) {
            $model->logToAssociatedRelationsThatRelatedInstanceIsRestored(['contacts', 'deals']);
        });

        static::deleting(function ($model, $repository) {
            if ($model->isForceDeleting()) {
                $repository->purge($model);
            } else {
                $model->logToAssociatedRelationsThatRelatedInstanceIsTrashed(['contacts', 'deals'], [
                     'key'   => 'company.timeline.associate_trashed',
                     'attrs' => ['companyName' => $model->display_name],
                 ]);
            }
        });
    }

    /**
     * Purge the company data
     *
     * @param \App\Models\Company $company
     *
     * @return void
     */
    protected function purge($company)
    {
        foreach (['contacts', 'emails', 'deals', 'activities'] as $relation) {
            tap($company->{$relation}(), function ($query) {
                if ($query->getModel()->usesSoftDeletes()) {
                    $query->withTrashed();
                }

                $query->detach();
            });
        }

        $company->parents()->update(['parent_company_id' => null]);

        $company->loadMissing(['notes', 'calls']);
        resolve(NoteRepository::class)->delete($company->notes);
        resolve(CallRepository::class)->delete($company->calls);
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
                'parents',
                'media',
                'changelog',
                'changelog.pinnedTimelineSubjects',
                'contacts.phones', // for calling
                'deals.stage', 'deals.pipeline', 'deals.pipeline.stages' => function ($query) {
                    return $query->orderBy('display_order');
                },
            ];
    }
}
