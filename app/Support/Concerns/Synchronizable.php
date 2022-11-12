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

namespace App\Support\Concerns;

use App\Models\Synchronization;

trait Synchronizable
{
    /**
     * Boot the Synchronizable trait
     *
     * @return void
     */
    public static function bootSynchronizable()
    {
        // Start a new synchronization once created.
        static::created(function ($synchronizable) {
            $synchronizable->synchronization()->create();
        });

        // Stop and delete associated synchronization.
        static::deleting(function ($synchronizable) {
            $synchronizable->synchronization->delete();
        });
    }

    /**
     * Get the synchronizable synchronizer class
     *
     * @return \App\Contracts\Synchronizable
     */
    abstract public function synchronizer();

    /**
     * Get the model synchronization model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function synchronization()
    {
        return $this->morphOne(Synchronization::class, 'synchronizable');
    }
}
