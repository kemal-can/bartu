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

use App\Innoclapps\Filters\Select;
use App\Enums\DealStatus as StatusEnum;

class DealStatus extends Select
{
    /**
     * Initialize Source class
     */
    public function __construct()
    {
        parent::__construct('status', __('deal.status.status'));

        $this->options(collect(StatusEnum::names())->mapWithKeys(function ($status) {
            return [$status => __('deal.status.' . $status)];
        })->all());

        $this->query(function ($builder, $value, $condition, $sqlOperator) {
            return $builder->where($this->field, $sqlOperator['operator'], StatusEnum::find($value), $condition);
        });
    }
}
