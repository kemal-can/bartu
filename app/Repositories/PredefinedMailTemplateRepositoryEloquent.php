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

namespace App\Repositories;

use App\Models\PredefinedMailTemplate;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\PredefinedMailTemplateRepository;

class PredefinedMailTemplateRepositoryEloquent extends AppRepository implements PredefinedMailTemplateRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return PredefinedMailTemplate::class;
    }
}
