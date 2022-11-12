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

namespace App\Models;

use App\Enums\SyncState;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Concerns\HasUuid;
use App\Contracts\SynchronizesViaWebhook;

class Synchronization extends Model
{
    use HasUuid;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'last_synchronized_at' => 'datetime',
        'start_sync_from'      => 'datetime',
        'expires_at'           => 'datetime',
        'sync_state'           => SyncState::class,
    ];

    /**
     * Check whether the user disabled the sync
     *
     * @return boolean
     */
    public function isSyncDisabled()
    {
        return $this->sync_state === SyncState::DISABLED;
    }

    /**
     * Check whether the system disabled the sync
     *
     * @return boolean
     */
    public function isSyncStoppedBySystem()
    {
        return $this->sync_state === SyncState::STOPPED;
    }

    /**
     * Ping the snychronizable to synchronize the data
     *
     * @return mixed
     */
    public function ping()
    {
        return $this->synchronizable->synchronize();
    }

    /**
     * Start listening for changes
     *
     * @return void
     */
    public function startListeningForChanges()
    {
        return $this->synchronizable->synchronizer()->watch($this);
    }

    /**
     * Stop listening for changes
     *
     * @return void
     */
    public function stopListeningForChanges()
    {
        if (! $this->isSynchronizingViaWebhook()) {
            return;
        }

        $this->synchronizable->synchronizer()->unwatch($this);
    }

    /**
     * Refresh the synchronizable webhook
     *
     * @return static
     */
    public function refreshWebhook()
    {
        $this->stopListeningForChanges();

        // Update the UUID since the previous one has
        // already been associated to watcher
        $this->{static::getUuidColumnName()} = static::generateUuid();
        $this->save();

        $this->startListeningForChanges();

        return $this;
    }

    /**
     * Get the synchronizable model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function synchronizable()
    {
        return $this->morphTo();
    }

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($synchronization) {
            $synchronization->last_synchronized_at = now();
            $synchronization->start_sync_from = now();
        });

        static::created(function ($synchronization) {
            if ($synchronization->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
                $synchronization->startListeningForChanges();
            }

            $synchronization->ping();
        });

        static::deleting(function ($synchronization) {
            if ($synchronization->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
                try {
                    $synchronization->stopListeningForChanges();
                } catch (\Exception $e) {
                }
            }
        });
    }

    /**
     * Check whether the synchronization is currently synchronizing via webhook
     *
     * @return boolean
     */
    public function isSynchronizingViaWebhook() : bool
    {
        return ! is_null($this->resource_id);
    }

    /**
       * Get the model uuid column name
       *
       * @return string
       */
    protected static function getUuidColumnName() : string
    {
        return 'id';
    }
}
