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

use App\Resources\Prunable;
use App\Contracts\Attendeeable;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Media\HasMedia;
use App\Support\Concerns\HasDeals;
use App\Support\Concerns\HasEmails;
use App\Support\Concerns\HasPhones;
use App\Support\Concerns\HasSource;
use App\Innoclapps\Concerns\HasUuid;
use App\Innoclapps\Concerns\HasAvatar;
use App\Innoclapps\Concerns\HasCountry;
use App\Innoclapps\Concerns\HasCreator;
use App\Support\Concerns\HasActivities;
use App\Innoclapps\Timeline\HasTimeline;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\ChangeLoggers\LogsModelChanges;
use App\Innoclapps\Workflow\HasWorkflowTriggers;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Innoclapps\Contracts\Fields\HandlesChangedMorphManyAttributes;

class Contact extends Model implements Presentable, HandlesChangedMorphManyAttributes, Attendeeable
{
    use HasAvatar,
        HasCountry,
        HasCreator,
        HasSource,
        LogsModelChanges,
        HasUuid,
        HasMedia,
        Resourceable,
        HasWorkflowTriggers,
        HasTimeline,
        HasEmails,
        HasDeals,
        HasActivities,
        HasFactory,
        HasPhones,
        SoftDeletes,
        Prunable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_by',
        'created_at',
        'updated_at',
        'owner_assigned_date',
        'next_activity_id',
        'uuid',
    ];

    /**
     * Attributes and relations to log changelog for the model
     *
     * @var array
     */
    protected static $changelogAttributes = [
        '*',
        'user.name',
        'country.name',
        'source.name',
    ];

    /**
     * Exclude attributes from the changelog
     *
     * @var array
     */
    protected static $changelogAttributeToIgnore = [
        'updated_at',
        'created_at',
        'created_by',
        'owner_assigned_date',
        'next_activity_id',
    ];

    /**
     * Provides the relationships for the pivot logger
     *
     * [ 'main' => 'reverse']
     *
     * @return array
     */
    protected static $logPivotEventsOn = [
        'companies' => 'contacts',
        'deals'     => 'contacts',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'owner_assigned_date' => 'datetime',
        'user_id'             => 'int',
        'created_by'          => 'int',
        'source_id'           => 'int',
        'country_id'          => 'int',
        'next_activity_id'    => 'int',
    ];

    /**
     * Get all of the companies that are associated with the contact
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function companies()
    {
        return $this->morphedByMany(\App\Models\Company::class, 'contactable')->withTimestamps()->orderBy('contactables.created_at');
    }

    /**
     * Get all of the associated activities for the record
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function activities()
    {
        return $this->morphToMany(\App\Models\Activity::class, 'activityable');
    }

    /**
     * Get all of the notes for the contact
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function notes()
    {
        return $this->morphToMany(\App\Models\Note::class, 'noteable');
    }

    /**
     * Get all of the calls for the contact
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function calls()
    {
        return $this->morphToMany(\App\Models\Call::class, 'callable');
    }

    /**
     * Get all of the contact guests models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function guests()
    {
        return $this->morphMany(\App\Models\Guest::class, 'guestable');
    }

    /**
     * Get the contact owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the model display name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function displayName() : Attribute
    {
        return Attribute::get(fn () => trim("$this->first_name $this->last_name"));
    }

    /**
     * Get the URL path
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::get(fn () => "/contacts/{$this->id}");
    }

    /**
     * Get the person email address when guest
     *
     * @return string|null
     */
    public function getGuestEmail() : ?string
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
        return $this->display_name;
    }

    /**
     * Get the notification that should be sent to the person when is added as guest
     *
     * @return \Illuminate\Mail\Mailable|\Illuminate\Notifications\Notification
     */
    public function getAttendeeNotificationClass()
    {
        return \App\Mail\ContactAttendsToActivity::class;
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
        return (bool) settings('send_contact_attends_to_activity_mail');
    }

    /**
     * Raw concat attributes for query
     *
     * @param array $attributes
     * @param string $separator
     *
     * @return \Illuminate\Database\Query\Expression|null
     */
    public static function nameQueryExpression($as = null)
    {
        $driver = (new static)->getConnection()->getDriverName();

        switch ($driver) {
            case 'mysql':
            case 'pgsql':
            case 'mariadb':
            return \DB::raw('RTRIM(CONCAT(first_name, \' \', COALESCE(last_name, \'\')))' . ($as ? ' as ' . $as : ''));

            break;
            case 'sqlite':
            return \DB::raw('RTRIM(first_name || \' \' || last_name)' . ($as ? ' as ' . $as : ''));

            break;
        }
    }
}
