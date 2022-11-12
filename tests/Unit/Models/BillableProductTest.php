<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\BillableProduct;

class BillableProductTest extends TestCase
{
    public function test_billable_product_amount_calculation_is_performed_on_create()
    {
        $product = $this->createProductWithPrice();

        $this->assertGreaterThan(0, $product->amount);
    }

    public function test_billable_product_amount_calculation_is_performed_on_update()
    {
        $product       = $this->createProductWithPrice();
        $originalAmont = $product->amount;

        $product->qty = 5;
        $product->save();

        $this->assertNotEquals($originalAmont, $product->amount);
    }

    public function test_billable_product_has_original_product()
    {
        $originalProduct = Product::factory();
        $product         = BillableProduct::factory()->for($originalProduct, 'originalProduct')->create();
        $this->assertInstanceOf(Product::class, $product->originalProduct);
    }

    public function test_billable_product_tax_rate_is_always_zero_when_billable_has_no_tax()
    {
        $product = $this->createProductWithPrice('no_tax', ['tax_rate' => 10]);

        $this->assertEquals(0, $product->tax_rate);
    }

    public function test_billable_product_has_sku()
    {
        $originalProduct = Product::factory(['sku' => 'SKU:123']);
        $product         = BillableProduct::factory()->for($originalProduct, 'originalProduct')->create();
        $this->assertEquals('SKU:123', $product->sku);
    }

    public function test_billable_product_has_no_sku_when_the_original_product_is_deleted()
    {
        $originalProduct = Product::factory(['sku' => 'SKU:123']);
        $product         = BillableProduct::factory()->for($originalProduct, 'originalProduct')->create();
        $product->originalProduct()->delete();
        $this->assertEmpty($product->sku);
    }

    public function test_billable_product_amounts_without_tax_billable()
    {
        $product = $this->createProductWithPrice('no_tax');

        $this->assertEquals(4000, $product->amount);
        $this->assertEquals(4000, $product->totalAmountWithDiscount());
        $this->assertEquals(0, $product->totalDiscountAmount());
        $this->assertEquals(0, $product->totalTaxAmount());
        $this->assertEquals(4000, $product->totalAmount());
        $this->assertEquals(4000, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('no_tax', ['discount_type' => 'fixed', 'discount_total' => 200]);

        $this->assertEquals(3800, $product->amount);
        $this->assertEquals(3800, $product->totalAmountWithDiscount());
        $this->assertEquals(200, $product->totalDiscountAmount());
        $this->assertEquals(0, $product->totalTaxAmount());
        $this->assertEquals(3800, $product->totalAmount());
        $this->assertEquals(3800, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('no_tax', ['discount_type' => 'percent', 'discount_total' => 10]);

        $this->assertEquals(3600, $product->amount);
        $this->assertEquals(3600, $product->totalAmountWithDiscount());
        $this->assertEquals(400, $product->totalDiscountAmount());
        $this->assertEquals(0, $product->totalTaxAmount());
        $this->assertEquals(3600, $product->totalAmount());
        $this->assertEquals(3600, $product->totalAmountBeforeTax());
    }

    public function test_billable_product_amounts_with_exclusive_tax_billable()
    {
        $product = $this->createProductWithPrice('exclusive', ['tax_rate' => 10]);

        $this->assertEquals(4000, $product->amount);
        $this->assertEquals(4000, $product->totalAmountWithDiscount());
        $this->assertEquals(0, $product->totalDiscountAmount());
        $this->assertEquals(400, $product->totalTaxAmount());
        $this->assertEquals(4400, $product->totalAmount());
        $this->assertEquals(4000, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('exclusive', ['tax_rate' => 10, 'discount_type' => 'fixed', 'discount_total' => 200]);

        $this->assertEquals(3600, $product->amount);
        $this->assertEquals(3800, $product->totalAmountWithDiscount());
        $this->assertEquals(200, $product->totalDiscountAmount());
        $this->assertEquals(360, $product->totalTaxAmount());
        $this->assertEquals(3960, $product->totalAmount());
        $this->assertEquals(3600, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('exclusive', ['tax_rate' => 10, 'discount_type' => 'percent', 'discount_total' => 10]);

        $this->assertEquals(3200, $product->amount);
        $this->assertEquals(3600, $product->totalAmountWithDiscount());
        $this->assertEquals(400, $product->totalDiscountAmount());
        $this->assertEquals(320, $product->totalTaxAmount());
        $this->assertEquals(3520, $product->totalAmount());
        $this->assertEquals(3200, $product->totalAmountBeforeTax());
    }

    public function test_billable_product_amounts_with_inclusive_tax_billable()
    {
        $product = $this->createProductWithPrice('inclusive', ['tax_rate' => 10]);

        $this->assertEquals(3636.36, $product->amount);
        $this->assertEquals(4000, $product->totalAmountWithDiscount());
        $this->assertEquals(0, $product->totalDiscountAmount());
        $this->assertEquals(363.64, $product->totalTaxAmount());
        $this->assertEquals(4000, $product->totalAmount());
        $this->assertEquals(3636.36, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('inclusive', ['tax_rate' => 10, 'discount_type' => 'fixed', 'discount_total' => 200]);

        $this->assertEquals(3272.73, $product->amount);
        $this->assertEquals(3800, $product->totalAmountWithDiscount());
        $this->assertEquals(200, $product->totalDiscountAmount());
        $this->assertEquals(327.27, $product->totalTaxAmount());
        $this->assertEquals(3600, $product->totalAmount());
        $this->assertEquals(3272.73, $product->totalAmountBeforeTax());

        $product = $this->createProductWithPrice('inclusive', ['tax_rate' => 10, 'discount_type' => 'percent', 'discount_total' => 10]);

        $this->assertEquals(2909.09, $product->amount);
        $this->assertEquals(3600, $product->totalAmountWithDiscount());
        $this->assertEquals(400, $product->totalDiscountAmount());
        $this->assertEquals(290.91, $product->totalTaxAmount());
        $this->assertEquals(3200, $product->totalAmount());
        $this->assertEquals(2909.09, $product->totalAmountBeforeTax());
    }

    protected function createProductWithPrice($taxType = null, $attributes = [])
    {
        if ($taxType === 'no_tax' || $taxType === null) {
            $taxTypeMethod = 'withBillableWithoutTax';
        } elseif ($taxType === 'exclusive') {
            $taxTypeMethod = 'withTaxExclusiveBillable';
        } else {
            $taxTypeMethod = 'withTaxInclusiveBillable';
        }

        return BillableProduct::factory()->{$taxTypeMethod}()->create(array_merge([
                'unit_price' => 2000,
                'qty'        => 2,
                'tax_rate'   => 0,
            ], $attributes));
    }
}
