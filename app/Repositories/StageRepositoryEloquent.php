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

use App\Models\User;
use App\Models\Stage;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;

class StageRepositoryEloquent extends AppRepository implements StageRepository
{
    /**
    * Searchable fields
    *
    * @var array
    */
    protected static $fieldSearchable = [
        'name' => 'like',
    ];

    /**
    * Specify Model class name
    *
    * @return string
    */
    public static function model()
    {
        return Stage::class;
    }

    /**
    * Find stages by the given pipeline
    *
    * @param int $id
    *
    * @return \Illuminate\Database\Collection
    */
    public function getByPipeline(int $id)
    {
        return $this->findWhere(['pipeline_id' => $id]);
    }

    /**
    * Get all stages for that can be used on option fields
    *
    * @param \App\Models\User $user
    *
    * @return \Illuminate\Support\Collection
    */
    public function allStagesForOptions(User $user)
    {
        $stages = $this->with('pipeline')
            ->whereHas('pipeline', fn ($query) => $query->visible($user))
            ->all();

        return $stages->map(fn ($stage) => [
            'id'   => $stage->getKey(),
            'name' => "{$stage->name} ({$stage->pipeline->name})",
        ]);
    }

    /**
    * Boot the repository
    *
    * @return void
    */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->deals()->count() > 0) {
                abort(409, __('deal.stage.delete_usage_warning'));
            }

            // We must delete the trashed deals when the stage is deleted
            // as we don't have any option to do with the deal except to associate
            // it with other stage (if found) but won't be accurate.
            $model->deals()->onlyTrashed()->get()->each(function ($deal) {
                app(DealRepository::class)->forceDelete($deal->getKey());
            });
        });
    }
}
