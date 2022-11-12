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

use App\Innoclapps\Fields\MorphToMany;
use App\Http\Resources\CompanyResource;

class Companies extends MorphToMany
{
    /**
     * Field order
     *
     * @var integer
     */
    public ?int $order = 1001;

    /**
     * Create new instance of Companies field
     *s
     * @param string $companies
     * @param string $label Custom label
     */
    public function __construct($relation = 'companies', $label = null)
    {
        parent::__construct($relation, $label ?? __('company.companies'));

        $this->setJsonResource(CompanyResource::class)
            ->labelKey('name')
            ->valueKey('id')
            ->excludeFromExport()
            ->excludeFromImport()
            ->excludeFromZapierResponse()
            ->tapIndexColumn(function ($column) {
                if (! $this->counts()) {
                    $column->useComponent('table-presentable-data-column');
                }
            })
            ->async('/companies/search')
            ->provideSampleValueUsing(fn () => [1, 2]);
    }
}
