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

use App\Models\Phone;

trait HasPhones
{
    /**
     * Boot the HasPhones trait
     *
     * @return void
     */
    protected static function bootHasPhones()
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->phones()->delete();
            }
        });
    }

    /**
     * A model has phone number
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable')->orderBy('phones.created_at');
    }

    /**
     * Handle the attributes updated event
     *
     * @param array $new
     * @param array $old
     *
     * @return void
     */
    public function morphManyAtributesUpdated($relationName, $new, $old)
    {
        if ($relationName === 'phones') {
            $valueProvider = function ($changed) {
                return collect($changed)->pluck('number')->filter()->implode(', ');
            };

            static::logDirtyAttributesOnLatestLog([
                'attributes' => ['phone' => $valueProvider($new)],
                'old'        => ['phone' => $valueProvider($old)],
            ], $this);
        }
    }
}
