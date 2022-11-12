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

namespace App\Support\Filters;

use App\Innoclapps\Filters\Text;
use App\Innoclapps\Filters\Number;
use App\Innoclapps\Filters\HasMany;
use App\Innoclapps\Filters\Operand;
use App\Innoclapps\QueryBuilder\Parser;

class BillableProductsFilter extends HasMany
{
    /**
     * Initialize BillableProductsFilter class
     *
     * @param string $singularLabel
     */
    public function __construct()
    {
        parent::__construct('products', __('product.products'));

        $this->setOperands([
            (new Operand('total_count', __('product.total_products')))->filter(
                Number::make('total_count')->countableRelation('products')
            ),
            (new Operand('name', __('product.product_name')))->filter(
                Text::make('name')
            ),
            (new Operand('qty', __('product.product_quantity')))->filter(
                Number::make('qty')
            ),
            (new Operand('unit', __('product.product_unit')))->filter(
                Text::make('unit')
            ),
            (new Operand('sku', __('product.product_sku')))->filter(
                Text::make('sku')->query(function ($builder, $value, $condition, $sqlOperator, $rule, Parser $parser) {
                    return $builder->whereHas(
                        'originalProduct',
                        function ($query) use ($value, $parser, $rule, $condition, $sqlOperator) {
                            return $parser->convertToQuery($query, $rule, $value, $sqlOperator['operator'], $condition);
                        }
                    );
                })
            ),
        ]);
    }
}
