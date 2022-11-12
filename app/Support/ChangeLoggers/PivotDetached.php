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

class PivotDetached extends PivotLogger
{
    /**
     * Log pivot detached event
     *
     * Used e.q. when detaching company
     *
     * @param \App\Innoclapps\Models\Model $detachedFrom The model where the pivot is detached
     * @param array $pivotIds Attached pivot IDs
     * @param string $identifier The relation name the event occured
     *
     * @return null
     */
    public static function log($detachedFrom, $pivotIds, $relation)
    {
        $pivotModels = static::getRelatedPivotIds($detachedFrom, $pivotIds, $relation);

        foreach ($pivotModels as $pivotModel) {
            static::perform($detachedFrom, $pivotModel, $relation, 'detached');
        }
    }
}
