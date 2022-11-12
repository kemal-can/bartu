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

namespace App\Resources\Source\Filters;

use App\Innoclapps\Filters\Select;
use App\Innoclapps\Facades\Innoclapps;

class Source extends Select
{
    /**
     * Initialize Source class
     */
    public function __construct()
    {
        parent::__construct('source_id', __('fields.companies.source.name'));

        $this->withNullOperators()
            ->valueKey('id')
            ->labelKey('name')
            ->options(Innoclapps::resourceByName('sources'));
    }
}
