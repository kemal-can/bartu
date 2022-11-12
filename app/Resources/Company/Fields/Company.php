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

namespace App\Resources\Company\Fields;

use App\Innoclapps\Fields\BelongsTo;
use App\Http\Resources\CompanyResource;
use App\Contracts\Repositories\CompanyRepository;

class Company extends BelongsTo
{
    /**
     * Create new instance of Company field
     *
     * @param string $relationName The relation name, snake case format
     * @param string $label Custom label
     * @param string $foreignKey Custom foreign key
     */
    public function __construct($relationName = 'company', $label = null, $foreignKey = null)
    {
        parent::__construct($relationName, CompanyRepository::class, $label ?? __('company.company'), $foreignKey);

        $this->setJsonResource(CompanyResource::class)
            ->async('/companies/search');
    }
}
