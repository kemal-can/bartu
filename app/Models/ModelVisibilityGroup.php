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
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModelVisibilityGroup extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected string $dependsTable = 'model_visibility_group_dependents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type'];

    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Get all of the teams that belongs to the visibility group
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function teams()
    {
        return $this->morphedByMany(\App\Models\Team::class, 'dependable', $this->dependsTable);
    }

    /**
     * Get all of the users that belongs to the visibility group
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users()
    {
        return $this->morphedByMany(\App\Models\User::class, 'dependable', $this->dependsTable);
    }

    /**
    * Get the parent model which uses visibility dependents
    */
    public function visibilityable()
    {
        return $this->morphTo();
    }
}
