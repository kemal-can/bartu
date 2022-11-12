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

namespace App\Innoclapps\Casts;

use App\Innoclapps\Date\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ISO8601DateTime implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param string|null $value
     * @param array $attributes
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function get($model, $key, $value, $attributes)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::createFromFormat(
            $model->getDateFormat(),
            $value
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if (empty($value)) {
            return null;
        }

        if (Carbon::isISO8601($value)) {
            $value = Carbon::parse($value)->inAppTimezone();
        }

        return $model->fromDateTime($value);
    }
}
