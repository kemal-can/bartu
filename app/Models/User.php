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
use App\Contracts\Attendeeable;
use App\Innoclapps\Models\Model;
use Laravel\Sanctum\HasApiTokens;
use App\Support\Concerns\HasTeams;
use App\Innoclapps\Concerns\HasMeta;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Contracts\Metable;
use App\Innoclapps\Models\ZapierHook;
use App\Innoclapps\Concerns\HasAvatar;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Support\Concerns\HasEditorFields;
use App\Innoclapps\Contracts\Localizeable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use App\Innoclapps\Contracts\Repositories\FilterRepository;
use App\Innoclapps\Contracts\Repositories\DashboardRepository;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements
    HasLocalePreference,
    Localizeable,
    Metable,
    Attendeeable,
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Notifiable,
     HasApiTokens,
     HasRoles,
     HasMeta,
     HasEditorFields,
     HasFactory,
     HasAvatar,
     Authenticatable,
     Authorizable,
     CanResetPassword,
     HasTeams;

    /**
     * Permissions guard name
     *
     * @var string
     */
    public $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'timezone',
        'date_format', 'time_format', 'first_day_of_week',
        'locale', 'mail_signature', 'super_admin', 'access_api',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_active_at'    => 'datetime',
        'super_admin'       => 'boolean',
        'access_api'        => 'boolean',
        'first_day_of_week' => 'int',
    ];

    /**
     * Boot User model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            static::createDefaults($user);
        });
    }

    /**
     * Check whether data can be synced to the user calendar
     *
     * @return boolean
     */
    public function canSyncToCalendar() : bool
    {
        return ! is_null($this->calendar) &&
            ! ($this->calendar->synchronization->isSyncDisabled() ||
                        $this->calendar->synchronization->isSyncStoppedBySystem());
    }

    /**
     * Get all the user connected oAuth accounts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oAuthAccounts()
    {
        return $this->hasMany(\App\Innoclapps\Models\OAuthAccount::class);
    }

    /**
     * Get the user connected oAuth calendar
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function calendar()
    {
        return $this->hasOne(\App\Models\Calendar::class)->whereHas('synchronization', function ($query) {
            return $query->where('sync_state', '!=', SyncState::DISABLED);
        });
    }

    /**
     * User can have many companies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class);
    }

    /**
     * User has many comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class, 'created_by');
    }

    /**
     * User can have many contacts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(\App\Models\Contact::class);
    }

    /**
     * User can have many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals()
    {
        return $this->hasMany(\App\Models\Deal::class);
    }

    /**
     * User has created many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdDeals()
    {
        return $this->hasMany(\App\Models\Deal::class, 'created_by');
    }

    /**
     * User can be assigned to many activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(\App\Models\Activity::class);
    }

    /**
     * User has many Zapier hooks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zapierHooks()
    {
        return $this->hasMany(ZapierHook::class);
    }

    /**
     * A user has many personal email accounts configured
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personalEmailAccounts()
    {
        return $this->hasMany(\App\Models\EmailAccount::class)
            ->whereBelongsTo($this)
            ->whereNotNull('user_id');
    }

    /**
     * A user has many predefined mail templates configured
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function predefinedMailTemplates()
    {
        return $this->hasMany(\App\Models\PredefinedMailTemplate::class);
    }

    /**
     * A user has many dashboards
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dashboards()
    {
        return $this->hasMany(\App\Innoclapps\Models\Dashboard::class);
    }

    /**
     * User can have many filters
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filters()
    {
        return $this->hasMany(\App\Innoclapps\Models\Filter::class);
    }

    /**
     * Check whether the user is super admin
     *
     * @return boolean
     */
    public function isSuperAdmin() : bool
    {
        return $this->super_admin === true;
    }

    /**
     * Check whether the user has api access
     *
     * @return boolean
     */
    public function hasApiAccess() : bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->access_api === true;
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale() : string
    {
        return $this->locale;
    }

    /**
     * Get the user time format
     *
     * @return string
     */
    public function getLocalTimeFormat() : string
    {
        return $this->time_format;
    }

    /**
     * Get the user date format
     *
     * @return string
     */
    public function getLocalDateFormat() : string
    {
        return $this->date_format;
    }

    /**
     * Get the user timezone
     *
     * @return string
     */
    public function getUserTimezone() : string
    {
        return $this->timezone;
    }

    /**
     * Latest 15 user notifications help relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function latestFifteenNotifications()
    {
        return $this->notifications()->take(15);
    }

    /**
     * Get all of the user guests models
     */
    public function guests()
    {
        return $this->morphMany(\App\Models\Guest::class, 'guestable');
    }

    /**
     * Get all of the activities the user created
     */
    public function createdActivities()
    {
        return $this->hasMany(\App\Models\Activity::class, 'created_by');
    }

    /**
     * Get all of the visibility dependent models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function visibilityDependents()
    {
        return $this->morphMany(ModelVisibilityGroupDependent::class, 'dependable');
    }

    /**
     * Get the person email address when guest
     *
     * @return string
     */
    public function getGuestEmail() : string
    {
        return $this->email;
    }

    /**
     * Get the person displayable name when guest
     *
     * @return string
     */
    public function getGuestDisplayName() : string
    {
        return $this->name;
    }

    /**
     * Get the notification that should be sent to the person when is added as guest
     *
     * @return \Illuminate\Notifications\Notification
     */
    public function getAttendeeNotificationClass()
    {
        return \App\Notifications\UserAttendsToActivity::class;
    }

    /**
     * Indicates whether the notification should be send to the guest
     *
     * @param \App\Contracts\Attendeeable $model
     *
     * @return boolean
     */
    public function shouldSendAttendingNotification(Attendeeable $model) : bool
    {
        // Otherwise is handled via user profile notification settings
        // e.q. if user turned off, it won't be sent
        return ! $model->is(Auth::user());
    }

    /**
     * Get the fields that are used as editor
     *
     * @return string
     */
    public function getEditorFields() : string
    {
        return 'mail_signature';
    }

    /**
     * Create the user default data
     *
     * @param \App\Models\User $user
     *
     * @return void
     */
    protected static function createDefaults($user) : void
    {
        resolve(DashboardRepository::class)->createDefault($user);

        $filters = resolve(FilterRepository::class);

        if ($openDealsFilter = $filters->findByFlag('open-deals')) {
            $filters->markAsDefault($openDealsFilter->id, ['deals', 'deals-board'], $user->id);
        }

        if ($openActivitiesFilter = $filters->findByFlag('open-activities')) {
            $filters->markAsDefault($openActivitiesFilter->id, 'activities', $user->id);
        }
    }
}
