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

namespace App\Innoclapps\Filters;

use App\Innoclapps\Fields\HasOptions;
use App\Innoclapps\Fields\ChangesKeys;

class Optionable extends Filter
{
    use ChangesKeys,
        HasOptions;
}
