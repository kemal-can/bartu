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

namespace App\Models;

use App\Enums\TaxType;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Billable extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tax_type' => TaxType::class,
    ];

    /**
     * Indicates if the model has timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tax_type', 'terms', 'notes'];

    /**
     * Billable has many products
     */
    public function products()
    {
        return $this->hasMany(\App\Models\BillableProduct::class);
    }

    /**
     * Check whether the billable is tax exclusive
     *
     * @return boolean
     */
    public function isTaxExclusive() : bool
    {
        return $this->tax_type === TaxType::exclusive;
    }

    /**
     * Check whether the billable is tax inclusive
     *
     * @return boolean
     */
    public function isTaxInclusive() : bool
    {
        return $this->tax_type === TaxType::inclusive;
    }

    /**
     * Check whether the billable has tax
     *
     * @return boolean
     */
    public function isTaxable() : bool
    {
        return $this->tax_type !== TaxType::no_tax;
    }

    /**
    * Get the owning imageable model.
    */
    public function billableable()
    {
        return $this->morphTo();
    }

    /**
     * Get the subTotal attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function subTotal() : Attribute
    {
        return Attribute::get(fn () => static::round(
            $this->products->reduce(function ($total, $product) {
                return $total += $product->totalAmountWithDiscount();
            }, 0)
        ));
    }

    /**
     * Get the totalDiscount attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function totalDiscount() : Attribute
    {
        return Attribute::get(fn () => static::round(
            $this->products->reduce(function ($total, $product) {
                return $total + $product->totalDiscountAmount();
            }, 0)
        ));
    }

    /**
     * Get the hasDiscount attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function hasDiscount() : Attribute
    {
        return Attribute::get(fn () => $this->total_discount > 0);
    }

    /**
     * Get the totalTax attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function totalTax() : Attribute
    {
        return Attribute::get(fn () => static::round(
            collect($this->getAppliedTaxes())->reduce(function ($total, $tax) {
                return $total + $tax['total'];
            }, 0)
        ));
    }

    /**
    * Get the total attribute
    *
    * @return \Illuminate\Database\Eloquent\Casts\Attribute
    */
    public function total() : Attribute
    {
        return Attribute::get(fn () => static::round(
            $this->subTotal + (! $this->isTaxInclusive() ? $this->totalTax : 0)
        ));
    }

    /**
     * Get the applied taxes on the billable
     *
     * @return array
     */
    public function getAppliedTaxes() : array
    {
        if (! $this->isTaxable()) {
            return [];
        }

        return collect($this->products->unique(function ($product) {
            return $product->tax_label . $product->tax_rate;
        })
            ->sortBy('tax_rate')
            ->where('tax_rate', '>', 0)
            ->reduce(function ($groups, $tax) {
                $groups[] = [
                    'key'   => $tax->tax_label . $tax->tax_rate,
                    'rate'  => $tax->tax_rate,
                    'label' => $tax->tax_label,
                    'total' => $this->products->filter(function ($product) use ($tax) {
                        return $product->tax_label === $tax->tax_label && $product->tax_rate === $tax->tax_rate;
                    })->reduce(fn ($total, $product) => $total + $this->totalTaxInAmount(
                        $product->totalAmountWithDiscount(),
                        $product->tax_rate,
                        $this->isTaxInclusive()
                    ), 0),
                ];

                return $groups;
            }, []))->map(function ($tax) {
                $tax['total'] = static::round($tax['total']);

                return $tax;
            })->all();
    }

    /**
     * Round the given number/money
     *
     * @param mixed $number
     *
     * @return float
     */
    public static function round($number)
    {
        return floatval(
            number_format($number, currency(Innoclapps::currency())->getPrecision(), '.', '')
        );
    }

    /**
     * Calculate total tax in the given amount for the given tax rate
     *
     * @param float $fromAmount
     * @param float $taxRate
     * @param boolean $isTaxInclusive
     *
     * @return float
     */
    protected function totalTaxInAmount($fromAmount, $taxRate, $isTaxInclusive)
    {
        if ($isTaxInclusive) {
            // [(Unit Price) – (Unit Price / (1+ Tax %))]
            return ($fromAmount) - ($fromAmount / (1 + ($taxRate / 100)));
        }

        // ((Unit Price) x (Tax %))
        return ($fromAmount * ($taxRate / 100));
    }

    /**
     * Get the billable products default tax type
     *
     * @return \App\Enums\TaxType|null
     */
    public static function defaultTaxType() : ?TaxType
    {
        $default = settings('tax_type');

        if ($default) {
            return TaxType::find($default);
        }

        return null;
    }

    /**
     * Set the billable products default tax type
     *
     * @param null|string|\App\Enums\TaxType $value
     *
     * @return void
     */
    public static function setDefaultTaxType(null|string|TaxType $value) : void
    {
        settings(['tax_type' => $value instanceof TaxType ? $value->name : $value]);
    }
}
