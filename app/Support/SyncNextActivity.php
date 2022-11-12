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

namespace App\Support;

use Batch;
use App\Models\Activity;
use App\Innoclapps\Facades\Innoclapps;
use App\Support\Concerns\HasActivities;
use Illuminate\Support\Facades\Artisan;
use App\Contracts\Repositories\ActivityRepository;

class SyncNextActivity
{
    /**
     * @var \App\Contracts\Repositories\ActivityRepository
     */
    protected ActivityRepository $activities;

    /**
     * Initialize SyncNextActivity
     */
    public function __construct()
    {
        $this->activities = resolve(ActivityRepository::class);
    }

    /**
     * Sync the resources next activity date
     *
     * @return void
     */
    public function __invoke()
    {
        foreach (static::getResourcesWithNextActivity() as $resource) {
            $resource->repository()->unguarded(function ($repository) {
                $this->syncFinished($repository);
                $this->syncNextActivity($repository->resetScope());
            });
        }
    }

    /**
     * Get all of the resources with next activity
     *
     * @return \Illuminate\Supoprt\Collection
     */
    public static function getResourcesWithNextActivity()
    {
        return Innoclapps::registeredResources()->filter(function ($resource) {
            return in_array(HasActivities::class, class_uses_recursive($resource->model()));
        });
    }

    /**
     * Sync the resource next activity
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     *
     * @return void
     */
    protected function syncNextActivity($repository)
    {
        $now = now();

        $callback = function ($query) use ($now) {
            return $query->incomplete()
                ->where(Activity::dueDateQueryExpression(), '>=', $now)
                ->orderBy(Activity::dueDateQueryExpression());
        };

        $recordsToUpdate = [];
        $resourceModel   = $repository->getModel();

        $repository->columns($resourceModel->getKeyName())
            ->whereHas('activities', $callback)
            ->with(['activities' => function ($query) use ($callback) {
                return $callback($query)->select(['id', 'due_date', 'due_time']);
            }])
            ->all()
            ->each(function ($record) use (&$recordsToUpdate) {
                $recordsToUpdate[] = [
                    $record->getKeyName() => $record->getKey(),
                    'next_activity_id'    => $record->activities->first()->getKey(),
                ];
            });

        $this->performBatchUpdate($recordsToUpdate, $resourceModel);
    }

    /**
     * Sync the finished resources
     *
     * Update next activity date to null where the resources doesn't have any incomplete activities
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     *
     * @return void
     */
    protected function syncFinished($repository)
    {
        $repository->scopeQuery(function ($query) {
            return $query->whereDoesntHave('activities', function ($query) {
                return $query->incomplete()->where(Activity::dueDateQueryExpression(), '>=', now());
            });
        })->massUpdate(['next_activity_id' => null]);
    }

    /**
     * Perform batch next activity date update
     *
     * @param array $records
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected function performBatchUpdate($records, $model)
    {
        $modifiedRecords = Batch::update($model, $records, $model->getKeyName());

        if ($modifiedRecords) {
            // Clear the cache as the Batch updater is using direct connection
            // to the database in this case, the model event won't be triggered
            // Artisan::call('modelCache:clear', ['--model' => $model::class]);
        }
    }
}
