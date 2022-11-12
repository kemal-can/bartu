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

namespace App\Support\ChangeLoggers;

use Illuminate\Support\Str;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Facades\ChangeLogger;

class PivotLogger
{
    /**
    * Perform the pivot activity log
    *
    * @param \Illuminate\Database\Eloquent\Model $actionOn The action attached|detached is performed to
    * @param \App\Innoclapps\Contracts\Presentable $pivotModel The pivot model
    * @param string $relation
    * @param string $action attached|detached
    *
    * @return mixed
    */
    protected static function perform($actionOn, Presentable $pivotModel, $relation, $action)
    {
        return ChangeLogger::onModel($actionOn, [
                'id'   => $pivotModel->getKey(),
                'name' => $pivotModel->display_name,
                'path' => $pivotModel->path,
                'lang' => [
                    'key' => Str::singular($relation) . '.timeline.' . $action,
                ],
            ])
            ->identifier($action)
            // Always add on second to the created_at as usually the log is performed
            // after a record is created and they won't be displayed properly on the timeline
            // e.q. the associated will be first, then created
            ->createdAt(now()->addSecond(1))
            ->log();
    }

    /**
    * Get the related pivot models by the pivot id's
    *
    * @param \App\Innoclapps\Models\Model $model
    * @param array $pivotIds
    * @param string $relation The main model relation
    *
    * @return Collection
    */
    public static function getRelatedPivotIds($model, $pivotIds, $relation)
    {
        $query = $model->{$relation}()->getModel()->query();

        if ($query->getModel()->usesSoftDeletes()) {
            $query->withTrashed();
        }

        return $query->whereIn($model->getKeyName(), $pivotIds)
            ->get();
    }
}
