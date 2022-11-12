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

namespace App\Innoclapps\Fields;

trait HasModelEvents
{
    /**
     * Handle the resource record "creating" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordCreating($model)
    {
        //
    }

    /**
     * Handle the resource record "created" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordCreated($model)
    {
        //
    }

    /**
     * Handle the resource record "updating" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordUpdating($model)
    {
        //
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordUpdated($model)
    {
        //
    }

    /**
     * Handle the resource record "deleting" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordDeleting($model)
    {
        //
    }

    /**
     * Handle the resource record "deleted" event
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return void
     */
    public function recordDeleted($model)
    {
        //
    }
}
