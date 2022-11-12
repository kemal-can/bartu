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

namespace App\Resources\Call\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Presentation;
use App\Contracts\Repositories\CallRepository;
use App\Contracts\Repositories\UserRepository;

class LoggedCallsBySaleAgent extends Presentation
{
    /**
     * Axis Y Offset
     *
     * @var integer
     */
    public int $axisYOffset = 150;

    /**
    * Indicates whether the cart is horizontal
    *
    * @var boolean
    */
    public bool $horizontal = true;

    /**
    * The default renge/period selected
    *
    * @var integer
    */
    public string|int|null $defaultRange = 30;

    /**
     * Calculates logged calls by sales agent
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(CallRepository::class);

        return $this->byDays('date')->count($request, $repository, 'user_id')
            ->label(function ($value) {
                return $this->users()->find($value)->name;
            });
    }

    /**
    * Get the ranges available for the chart.
    *
    * @return array
    */
    public function ranges() : array
    {
        return [
            7   => __('dates.periods.7_days'),
            15  => __('dates.periods.15_days'),
            30  => __('dates.periods.30_days'),
            60  => __('dates.periods.60_days'),
            90  => __('dates.periods.90_days'),
            365 => __('dates.periods.365_days'),
        ];
    }

    /**
     * Get the all available users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function users()
    {
        return once(function () {
            return resolve(UserRepository::class)->columns(['id', 'name'])->all();
        });
    }

    /**
    * The card name
    *
    * @return string
    */
    public function name() : string
    {
        return __('call.cards.by_sale_agent');
    }
}
