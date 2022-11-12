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

namespace App\Innoclapps\Models;

class CustomFieldOption extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'custom_field_id' => 'int',
    ];

    /**
     * A custom field option belongs to custom field
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    /**
     * Determine if the model touches a given relation.
     * The custom field option touches all parent models
     *
     * For example, when record that is using custom field with options is updated
     * we need to update the record updated_at column.
     *
     * In this case, tha parent must use timestamps too.
     *
     * @param string $relation
     *
     * @return boolean
     */
    public function touches($relation)
    {
        return true;
    }
}
