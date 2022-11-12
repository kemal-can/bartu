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

use App\Enums\PhoneType;
use App\Innoclapps\Models\Model;
use App\Support\CountryCallingCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Innoclapps\Contracts\Fields\TracksMorphManyModelAttributes;

class Phone extends Model implements TracksMorphManyModelAttributes
{
    use HasFactory;

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['phoneable'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'type',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'type' => PhoneType::class,
    ];

    /**
     * Get the phoneables
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function phoneable()
    {
        return $this->morphTo();
    }

    /**
     * Get the attributes the changes should be tracked on
     *
     * @return string
     */
    public function trackAttributes() : string
    {
        return 'number';
    }

    /**
     * Generate random phone number
     *
     * @return string
     */
    public static function generateRandomPhoneNumber() : string
    {
        return CountryCallingCode::random() . mt_rand(100, 1000) . '-' . mt_rand(100, 1000) . '-' . mt_rand(100, 1000);
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            // For table serialization, will show the string value on the front-end
            'type' => $this->type->name,
        ]);
    }
}
