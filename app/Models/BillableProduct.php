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

use App\Innoclapps\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillableProduct extends Model
{
    use HasFactory;

    /**
     * The relationships that should always be loaded.
     *
     * Avoid lazy loading violation exception when saving products to Billable
     *
     * @var array
     */
    protected $with = ['billable', 'originalProduct'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'unit_price',
        'qty',
        'unit',
        'tax_rate',
        'tax_label',
        'discount_type',
        'discount_total',
        'display_order',
        'note',
        'product_id',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'unit_price'     => 'decimal:3',
        'tax_rate'       => 'decimal:3',
        'qty'            => 'decimal:2',
        'discount_total' => 'decimal:2',
        'amount'         => 'decimal:3',
        'billable_id'    => 'int',
        'display_order'  => 'int',
        'product_id'     => 'int',
    ];

    /**
     * Boot the BillableProduct model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->amount = $model->totalAmountBeforeTax();
        });

        static::updating(function ($model) {
            $model->amount = $model->totalAmountBeforeTax();
        });
    }

    /**
     * Get the underlying original product
     *
     * Note that the original product may be null as well if deleted
     */
    public function originalProduct()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    /**
     * Get the sku attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function sku() : Attribute
    {
        return Attribute::get(fn () => $this->originalProduct?->sku);
    }

    /**
     * A product belongs to a billable model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billable()
    {
        return $this->belongsTo(Billable::class);
    }

    /**
     * Get the taxRate attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function taxRate() : Attribute
    {
        return Attribute::get(function () {
            // In case the billable is saved with no tax but the product tax_rate attribute has tax
            if (! $this->billable->isTaxable()) {
                return 0;
            }

            return $this->castAttribute('tax_rate', $this->attributes['tax_rate'] ?? 0);
        });
    }

    /**
     * Get the product total amount with discount included
     *
     * @return int
     */
    public function totalAmountWithDiscount()
    {
        $unitPrice = $this->unit_price;
        $qty       = $this->qty;

        return Billable::round(
            ($unitPrice * $qty) - $this->totalDiscountAmount()
        );
    }

    /**
     * Get the product total discount amount
     *
     * @return int
     */
    public function totalDiscountAmount()
    {
        if ($this->discount_type === 'fixed') {
            return $this->discount_total;
        }

        $discountRate = $this->discount_total;
        $unitPrice    = $this->unit_price;
        $qty          = $this->qty;

        if ($this->billable->isTaxInclusive()) {
            // (Discount %) x (Unit Price) x Qty
            return Billable::round(($discountRate / 100) * ($unitPrice) * $qty);
        }

        // (Discount %) x (Unit Price x Qty)
        return Billable::round(($discountRate / 100) * ($unitPrice * $qty));
    }

    /**
     * Get the product total tax amount
     *
     * @return float
     */
    public function totalTaxAmount()
    {
        if (! $this->billable->isTaxable()) {
            return 0;
        }

        $unitPrice = $this->unit_price;
        $qty       = $this->qty;
        $taxRate   = $this->tax_rate;

        if ($this->billable->isTaxInclusive()) {
            // Qty x [(Unit Price – Discount Amount) – (Unit Price – Discount Amount / (1+ Tax %))]
            $amount = $qty * (
                ($unitPrice - $this->totalDiscountAmount()) -
                ($unitPrice - $this->totalDiscountAmount()) / (1 + ($taxRate / 100))
            );
        } else {
            // Qty x ((Unit Price - Discount Amount) x (Tax %))
            $amount = $qty * (($unitPrice - $this->totalDiscountAmount()) * ($taxRate / 100));
        }

        return Billable::round($amount);
    }

    /**
     * Get the product total amount including taxes and discount
     *
     * @return float
     */
    public function totalAmount()
    {
        $taxAmount = $this->totalTaxAmount();

        // Tax amount + Amount before tax
        return Billable::round(
            ($taxAmount + $this->totalAmountBeforeTax())
        );
    }

    /**
     * Get the total product amount before tax
     *
     * @return float
     */
    public function totalAmountBeforeTax()
    {
        if (! $this->billable->isTaxable()) {
            return $this->totalAmountWithDiscount();
        }

        $unitPrice = $this->unit_price;
        $qty       = $this->qty;
        $taxRate   = $this->tax_rate;

        if ($this->billable->isTaxInclusive()) {
            // Qty x ((Unit Price – Discount Amount) / (1+ Tax %))
            $amount = $qty * (($unitPrice - $this->totalDiscountAmount()) / (1 + ($taxRate / 100)));
        } else {
            // Qty x (Unit Price – Discount Amount)
            $amount = $qty * ($unitPrice - $this->totalDiscountAmount());
        }

        return Billable::round($amount);
    }

    /**
     * Get the billable products default discount type
     *
     * @return string|null
     */
    public static function defaultDiscountType() : ?string
    {
        return settings('discount_type');
    }

    /**
     * Set the billable products default discount type
     *
     * @param string|null $value
     *
     * @return void
     */
    public static function setDefaultDiscountType(?string $value) : void
    {
        settings(['discount_type' => $value]);
    }

    /**
     * Get the billable products default tax label
     *
     * @return string|null
     */
    public static function defaultTaxLabel() : ?string
    {
        return settings('tax_label');
    }

    /**
     * Set the billable products default tax label
     *
     * @param string|null $value
     *
     * @return void
     */
    public static function setDefaultTaxLabel(?string $value) : void
    {
        settings(['tax_label' => $value]);
    }

    /**
     * Get the billable products default tax rate
     *
     * @return float|int|null
     */
    public static function defaultTaxRate() : float|int|null
    {
        return settings('tax_rate');
    }

    /**
     * Set the billable products default tax label
     *
     * @param float|int|null $value
     *
     * @return void
     */
    public static function setDefaultTaxRate(float|int|null $value) : void
    {
        settings(['tax_rate' => $value]);
    }
}
