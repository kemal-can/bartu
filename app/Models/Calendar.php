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

use App\Innoclapps\Models\Model;
use App\Calendar\CalendarSyncManager;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Models\OAuthAccount;
use App\Support\Concerns\Synchronizable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Innoclapps\OAuth\EmptyRefreshTokenException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contracts\Repositories\SynchronizationRepository;

class Calendar extends Model
{
    use Synchronizable, HasFactory;

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'activity_types'   => 'array',
        'data'             => 'array',
        'user_id'          => 'int',
        'activity_type_id' => 'int',
        'access_token_id'  => 'int',
    ];

    /**
     * An email account can belongs to a user (personal)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Innoclapps::getUserRepository()->model());
    }

    /**
     * A model has OAuth connection
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function oAuthAccount()
    {
        return $this->hasOne(OAuthAccount::class, 'id', 'access_token_id');
    }

    /**
     * Get the calendar synchronizer class
     *
     * @return \App\Contracts\Synchronizable
     */
    public function synchronizer()
    {
        return CalendarSyncManager::createClient($this);
    }

    /**
     * Synchronize the calendar events
     *
     * @return void
     */
    public function synchronize()
    {
        try {
            $this->synchronizer()->synchronize($this->synchronization);
        } catch (EmptyRefreshTokenException $e) {
            resolve(SynchronizationRepository::class)->stopSync(
                $this->synchronization->getKey(),
                'The sync for this calendar is disabled because the ' . $this->email . ' account has empty refresh token, try to remove the app from your ' . explode('@', $this->email)[1] . ' account connected apps section and re-connect again.'
            );
        }
    }

    /**
     * Get the date the events should be synced from
     *
     * @return \Carbon\Carbon
     */
    public function startSyncFrom()
    {
        return $this->synchronization->start_sync_from;
    }

    /**
     * Get the connectionType attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function connectionType() : Attribute
    {
        return Attribute::get(fn () => match ($this->oAuthAccount->type) {
            'microsoft'             => 'outlook',
            default                 => $this->oAuthAccount->type,
        });
    }
}
