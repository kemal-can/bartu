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

use App\Innoclapps\Facades\ChangeLogger;

trait LogsPivotEvents
{
    /**
     * Check whether should log the pivot activity
     * based on the pivot changed relation
     *
     * @param string $relationName
     *
     * @return boolean
     */
    protected static function shouldLogPivotEvents($relationName)
    {
        return property_exists(get_called_class(), 'logPivotEventsOn')
            && isset(static::$logPivotEventsOn[$relationName]);
    }

    /**
     * Log to the given associated relations that the related instance is trashed
     *
     * @param array $relations
     * @param array $langAttributes
     *
     * @return void
     */
    public function logToAssociatedRelationsThatRelatedInstanceIsTrashed(array $relations, array $langAttributes)
    {
        foreach ($relations as $relationname) {
            $query = $this->{$relationname}();

            if ($query->getModel()->usesSoftDeletes()) {
                $query->withTrashed();
            }

            $query->get()->each(function ($relatedModel) use ($langAttributes) {
                ChangeLogger::onModel($relatedModel, [
                    'icon' => 'Trash',
                    'lang' => $langAttributes,
                ])->log();
            });
        }
    }

    /**
     * Log to the given associated relations that the related instance is restored
     *
     * @param array $relations
     *
     * @return void
     */
    public function logToAssociatedRelationsThatRelatedInstanceIsRestored(array $relations)
    {
        foreach ($relations as $relationname) {
            $query = $this->{$relationname}();

            if ($query->getModel()->usesSoftDeletes()) {
                $query->withTrashed();
            }

            $query->get()->each(function ($relatedModel) {
                ChangeLogger::onModel($relatedModel, [
                    'icon' => 'Trash',
                    'lang' => [
                        'key'   => 'timeline.association_restored',
                        'attrs' => ['associationDisplayName' => $relatedModel->display_name],
                    ],
                ])->log();
            });
        }
    }

    /**
     * Boot the events callbacks
     *
     * @return void
     */
    public static function bootLogsPivotEvents()
    {
        static::pivotAttached(function ($instance, $secondInstance, $relationName, $pivotIdsAttributes) {
            if (! static::shouldLogPivotEvents($relationName)) {
                return;
            }

            PivotAttached::log($instance, $pivotIdsAttributes, $relationName);

            // Log the reverse relation
            $instance->{$relationName}()->findMany($pivotIdsAttributes)
                ->each(function ($pivotModel) use ($instance, $relationName) {
                    PivotAttached::log(
                        $pivotModel,
                        [$instance->id],
                        static::$logPivotEventsOn[$relationName]
                    );
                });
        });

        static::pivotDetached(function ($instance, $secondInstance, $relationName, $pivotIdsAttributes) {
            if (! static::shouldLogPivotEvents($relationName)) {
                return;
            }

            if (! method_exists($instance, 'isForceDeleting') || ! $instance->isForceDeleting()) {
                PivotDetached::log($instance, $pivotIdsAttributes, $relationName);
            }

            $pivotModelQuery = $instance->{$relationName}()->getModel()->query();

            if ($pivotModelQuery->getModel()->usesSoftDeletes()) {
                $pivotModelQuery = $pivotModelQuery->withTrashed();
            }

            $pivotModels = $pivotModelQuery->findMany($pivotIdsAttributes);

            if ($instance->isForceDeleting()) {
                $pivotModels->each(function ($relatedModel) use ($instance) {
                    return ChangeLogger::onModel($relatedModel, [
                         'icon' => 'Trash',
                         'lang' => [
                             'key'   => 'timeline.association_permanently_deleted',
                             'attrs' => ['associationDisplayName' => $instance->display_name],
                         ],
                     ])->log();
                });
            } else {
                // Log the reverse relation
                $pivotModels->each(function ($pivotModel) use ($instance, $relationName) {
                    PivotDetached::log(
                        $pivotModel,
                        [$instance->id],
                        static::$logPivotEventsOn[$relationName]
                    );
                });
            }
        });
    }
}
