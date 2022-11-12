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
use App\Innoclapps\Models\Model;
use App\Innoclapps\Media\HasMedia;
use App\Support\Concerns\HasDeals;
use App\Support\Concerns\HasEmails;
use App\Support\Concerns\HasPhones;
use App\Support\Concerns\HasSource;
use App\Innoclapps\Concerns\HasUuid;
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

class Company extends Model implements Presentable, HandlesChangedMorphManyAttributes
{
    use HasCountry,
        HasCreator,
        HasSource,
        LogsModelChanges,
        Resourceable,
        HasUuid,
        HasMedia,
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
        'country.name',
        'parent.name',
        'source.name',
        'user.name',
        'industry.name',
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
        'contacts' => 'companies',
        'deals'    => 'companies',
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
        'industry_id'         => 'int',
        'parent_company_id'   => 'int',
        'country_id'          => 'int',
        'next_activity_id'    => 'int',
    ];

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
     * Get the parent company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(\App\Models\Company::class, 'parent_company_id');
    }

    /**
     * Get all of the company parent companies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parents()
    {
        return $this->hasMany(\App\Models\Company::class, 'parent_company_id');
    }

    /**
     * Get all of the contacts that are associated with the company
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function contacts()
    {
        return $this->morphToMany(\App\Models\Contact::class, 'contactable')->withTimestamps()->orderBy('contactables.created_at');
    }

    /**
     * Get all of the notes for the company
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function notes()
    {
        return $this->morphToMany(\App\Models\Note::class, 'noteable');
    }

    /**
     * Get all of the calls for the company
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function calls()
    {
        return $this->morphToMany(\App\Models\Call::class, 'callable');
    }

    /**
     * Get the company owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the company industry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function industry()
    {
        return $this->belongsTo(\App\Models\Industry::class);
    }

    /**
     * Get the model display name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function displayName() : Attribute
    {
        return Attribute::get(fn () => $this->name);
    }

    /**
     * Get the URL path
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::get(fn () => "/companies/{$this->id}");
    }
}
