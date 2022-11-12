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

namespace App\Innoclapps\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Presentable
{
    public function displayName() : Attribute;

    public function path() : Attribute;

    public function getKeyName();

    public function getKey();
}
