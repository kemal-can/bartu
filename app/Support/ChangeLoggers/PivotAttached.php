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

class PivotAttached extends PivotLogger
{
    /**
     * Log pivot attached event
     *
     * Used e.q. when attaching company
     *
     * @param \App\Innoclapps\Models\Model $attachedTo The model where the pivot is attached
     * @param array $pivotIds Attached pivot IDs
     * @param string $relation The relation name the event occured
     *
     * @return null
     */
    public static function log($attachedTo, $pivotIds, $relation)
    {
        $pivotModels = static::getRelatedPivotIds($attachedTo, $pivotIds, $relation);

        foreach ($pivotModels as $pivotModel) {
            static::perform($attachedTo, $pivotModel, $relation, 'attached');
        }
    }
}
