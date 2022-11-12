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

namespace App\Innoclapps\Charts;

use Illuminate\Http\Request;
use App\Innoclapps\Cards\Card;

abstract class Chart extends Card
{
    /**
     * Indicates whether the chart values are amount
     *
     * @var boolean
     */
    protected bool $amountValue = false;

    /**
     * Chart color/variant class
     *
     * @var string|null
     */
    protected ?string $color = null;

    /**
     * The method to perform the line chart calculations
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    abstract public function calculate(Request $request);

    /**
     * The chart available labels
     *
     * @return array
     */
    public function labels($result) : array
    {
        return [];
    }

    /**
     * Set chart color
     *
     * @param string $color
     *
     * @return static
     */
    public function color(string $color) : static
    {
        $this->color = 'chart-' . $color;

        return $this;
    }

    /**
     * Prepate the data for the front-end
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'result'       => $this->calculate(request()),
            'amount_value' => $this->amountValue,
            'color'        => $this->color,
        ]);
    }
}
