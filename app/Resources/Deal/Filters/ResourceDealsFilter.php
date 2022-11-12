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

namespace App\Resources\Deal\Filters;

use App\Innoclapps\Filters\Date;
use App\Innoclapps\Filters\Number;
use App\Innoclapps\Filters\HasMany;
use App\Innoclapps\Filters\Numeric;
use App\Innoclapps\Filters\Operand;

class ResourceDealsFilter extends HasMany
{
    /**
     * Initialize ResourceDealsFilter class
     *
     * @param string $singularLabel
     */
    public function __construct($singularLabel)
    {
        parent::__construct('deals', __('deal.deals'));

        $this->setOperands([
            (new Operand('amount', __('deal.deal_amount')))->filter(
                Numeric::make('amount')
            ),
            (new Operand('expected_close_date', __('deal.deal_expected_close_date')))->filter(
                Date::make('expected_close_date')
            ),
            (new Operand('open_count', __('deal.count.open', ['resource' => $singularLabel])))->filter(
                Number::make('open_count')->countableRelation('authorizedOpenDeals')
            ),
            (new Operand('won_count', __('deal.count.won', ['resource' => $singularLabel])))->filter(
                Number::make('won_count')->countableRelation('authorizedWonDeals')
            ),
            (new Operand('lost_count', __('deal.count.lost', ['resource' => $singularLabel])))->filter(
                Number::make('lost_count')->countableRelation('authorizedLostDeals')
            ),
            (new Operand('closed_count', __('deal.count.closed', ['resource' => $singularLabel])))->filter(
                Number::make('closed_count')->countableRelation('authorizedClosedDeals')
            ),
        ]);
    }
}
