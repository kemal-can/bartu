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

namespace App\Innoclapps\Rules;

use App\Innoclapps\Makeable;
use Illuminate\Validation\Rules\Unique;

class UniqueRule extends Unique
{
    use Makeable;

    /**
     * Create a new rule instance.
     *
     * @param string $modelName
     * @param string|int|null $ignore
     * @param string $column
     *
     * @return void
     */
    public function __construct($modelName, $ignore = null, $column = 'NULL')
    {
        parent::__construct(
            app($modelName)->getTable(),
            $column
        );

        if (! is_null($ignore)) {
            $ignoredId = is_int($ignore) ? $ignore : (request()->route($ignore) ?: null);

            $this->ignore($ignoredId);
        }
    }
}
