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

namespace App\Innoclapps\Translation;

use JsonSerializable;
use Illuminate\Support\Arr;

class DotNotationResult implements JsonSerializable
{
    /**
     * Initialize DotNotation
     *
     * @param array $translations
     */
    public function __construct(protected array $translations)
    {
    }

    /**
     * Get the translations groups with dot notation
     *
     * @return array
     */
    public function groups() : array
    {
        return collect($this->translations)->mapWithKeys(function ($translations, $group) {
            return [$group => Arr::dot($translations)];
        })->all();
    }

    /**
     * Get clean array from group dot notation array
     *
     * @return array
     */
    public function clean() : array
    {
        $array = [];

        foreach ($this->translations as $key => $value) {
            Arr::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->groups();
    }
}
