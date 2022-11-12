<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Deal;
use App\Enums\TaxType;
use App\Models\Billable;
use App\Models\BillableProduct;
use Illuminate\Database\Eloquent\Factories\Sequence;

class BillableTest extends TestCase
{
    public function test_can_determine_whether_billable_is_tax_exclusive()
    {
        $billable = new Billable(['tax_type' => TaxType::exclusive]);

        $this->assertTrue($billable->isTaxExclusive());
    }

    public function test_can_determine_whether_billable_is_tax_inclusive()
    {
        $billable = new Billable(['tax_type' => TaxType::inclusive]);

        $this->assertTrue($billable->isTaxInclusive());
    }

    public function test_can_determine_when_the_billable_has_no_tax()
    {
        $billable = new Billable(['tax_type' => TaxType::no_tax]);

        $this->assertFalse($billable->isTaxable());
    }

    public function test_billable_has_total_tax_attribute()
    {
        $noTax = $this->makeBillableWithProducts()->noTax()->create();
        $this->assertEquals(0, $noTax->total_tax);

        $exclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxExclusive()->create();
        $this->assertEquals(400, $exclusive->total_tax);

        $inclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxInclusive();
        $this->assertEquals(363.64, $inclusive->create()->total_tax);
    }

    public function test_billable_has_sub_total_attribute()
    {
        $noTax = $this->makeBillableWithProducts()->noTax()->create();
        $this->assertEquals(4000, $noTax->sub_total);

        $noTaxWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200]
        )->noTax()->create();
        $this->assertEquals(3600, $noTaxWithFixedDiscount->sub_total);

        $noTaxWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10]
        )->noTax()->create();
        $this->assertEquals(3600, $noTaxWithPercentDiscount->sub_total);

        // Exclusive
        $exclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxExclusive()->create();
        $this->assertEquals(4000, $exclusive->sub_total);

        $exclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(3600, $exclusiveWithFixedDiscount->sub_total);

        $exclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(3600, $exclusiveWithPercentDiscount->sub_total);

        // Inclusive
        $inclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxInclusive()->create();
        $this->assertEquals(4000, $inclusive->sub_total);

        $inclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(3600, $inclusiveWithFixedDiscount->sub_total);

        $inclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(3600, $inclusiveWithPercentDiscount->sub_total);
    }

    public function test_no_tax_billable_total_attribute_is_calculated_properly()
    {
        $noTax = $this->makeBillableWithProducts()->noTax()->create();
        $this->assertEquals(4000, $noTax->total);

        $noTaxWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200]
        )->noTax()->create();
        $this->assertEquals(3600, $noTaxWithFixedDiscount->total);

        $noTaxWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10]
        )->noTax()->create();
        $this->assertEquals(3600, $noTaxWithPercentDiscount->total);
    }

    public function test_tax_exclusive_billable_total_attribute_is_calculated_properly()
    {
        $exclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxExclusive()->create();
        $this->assertEquals(4400, $exclusive->total);

        $exclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(3960, $exclusiveWithFixedDiscount->total);

        $exclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(3960, $exclusiveWithPercentDiscount->total);
    }

    public function test_tax_inclusive_billable_total_attribute_is_calculated_properly()
    {
        $inclusive = $this->makeBillableWithProducts(['tax_rate' => 10])->taxInclusive()->create();
        $this->assertEquals(4000, $inclusive->total);

        $inclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(3600, $inclusiveWithFixedDiscount->total);

        $inclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(3600, $inclusiveWithPercentDiscount->total);
    }

    public function test_billable_has_total_discount_attribute()
    {
        $noTaxWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200]
        )->noTax()->create();
        $this->assertEquals(400, $noTaxWithFixedDiscount->total_discount);

        $noTaxWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10]
        )->noTax()->create();
        $this->assertEquals(400, $noTaxWithPercentDiscount->total_discount);

        // Exclusive
        $exclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(400, $exclusiveWithFixedDiscount->total_discount);

        $exclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxExclusive()->create();
        $this->assertEquals(400, $exclusiveWithPercentDiscount->total_discount);

        // Inclusive
        $inclusiveWithFixedDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(400, $inclusiveWithFixedDiscount->total_discount);

        $inclusiveWithPercentDiscount = $this->makeBillableWithProducts(
            ['discount_type' => 'percent', 'discount_total' => 10, 'tax_rate' => 10]
        )->taxInclusive()->create();
        $this->assertEquals(400, $inclusiveWithPercentDiscount->total_discount);
    }

    public function test_can_determine_if_billable_has_discount_applied()
    {
        $billable = $this->makeBillableWithProducts(
            ['discount_type' => 'fixed', 'discount_total' => 200]
        )->taxInclusive()->create();

        $this->assertTrue($billable->has_discount);
    }

    public function test_billable_taxes_are_unique()
    {
        $billable = Billable::factory()->withBillableable()
            ->taxExclusive()
            ->create();

        $products = BillableProduct::factory()->count(4)->state(new Sequence(
            ['tax_label' => 'TAX1', 'tax_rate' => 10],
            ['tax_label' => 'TAX1', 'tax_rate' => 10],
            ['tax_label' => 'TAX1', 'tax_rate' => 15],
            ['tax_label' => 'TAX4', 'tax_rate' => 15],
        ))->create();

        $billable->products()->saveMany($products);

        $taxes = $billable->getAppliedTaxes();

        $this->assertCount(3, $taxes);
    }

    public function test_billable_taxes_are_calculated_properly()
    {
        $billable = Billable::factory()->withBillableable()
            ->taxExclusive()
            ->create();

        $products = BillableProduct::factory()->count(3)->state(new Sequence(
            ['tax_label' => 'TAX1', 'tax_rate' => 10],
            ['tax_label' => 'TAX1', 'tax_rate' => 10],
            ['tax_label' => 'TAX2', 'tax_rate' => 15]
        ))->create(['unit_price' => 2000]);

        $billable->products()->saveMany($products);
        $taxes = $billable->getAppliedTaxes();

        $this->assertEquals(400, $taxes[0]['total']);
        $this->assertEquals(300, $taxes[1]['total']);
    }

    public function test_billable_has_billableable()
    {
        $billable = Billable::factory()->withBillableable()->create();

        $this->assertInstanceOf(Deal::class, $billable->billableable);
    }

    public function test_billable_has_products()
    {
        $billable = $this->makeBillableWithProducts()->create();

        $this->assertCount(2, $billable->products);
    }

    protected function makeBillableWithProducts($attributes = [])
    {
        $callback = function () use ($attributes) {
            return array_merge([
                'unit_price' => 2000,
                'qty'        => 1,
                'tax_rate'   => 0,
            ], $attributes);
        };

        return Billable::factory()->withBillableable()
            ->has(BillableProduct::factory()->count(2)->state($callback), 'products');
    }
}
