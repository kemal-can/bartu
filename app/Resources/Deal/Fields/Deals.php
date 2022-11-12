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

namespace App\Resources\Deal\Fields;

use App\Http\Resources\DealResource;
use App\Innoclapps\Fields\MorphToMany;

class Deals extends MorphToMany
{
    /**
     * Field order
     *
     * @var integer
     */
    public ?int $order = 999;

    /**
     * Create new instance of Deals field
     *
     * @param string $relation
     * @param string $label Custom label
     */
    public function __construct($relation = 'deals', $label = null)
    {
        parent::__construct($relation, $label ?? __('deal.deals'));

        $this->setJsonResource(DealResource::class)
            ->labelKey('name')
            ->valueKey('id')
            ->excludeFromExport()
            ->excludeFromImport()
            ->tapIndexColumn(function ($column) {
                if (! $this->counts()) {
                    $column->useComponent('table-presentable-data-column');
                }
            })
            ->excludeFromZapierResponse()
            ->async('/deals/search')
            ->provideSampleValueUsing(fn () => [1, 2]);
    }
}
