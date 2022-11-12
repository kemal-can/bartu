<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\BillableProduct;

class ProductTest extends TestCase
{
    public function test_when_product_created_by_not_provided_uses_current_user_id()
    {
        $user = $this->signIn();

        $product = Product::factory(['created_by' => null])->create();

        $this->assertEquals($product->created_by, $user->id);
    }

    public function test_product_created_by_can_be_provided()
    {
        $user = $this->createUser();

        $product = Product::factory()->for($user, 'creator')->create();

        $this->assertEquals($product->created_by, $user->id);
    }

    public function test_product_has_path_attribute()
    {
        $product = Product::factory()->create();

        $this->assertEquals('/products/1', $product->path);
    }

    public function test_product_has_display_name_attribute()
    {
        $product = Product::factory(['name' => 'Product name'])->make();

        $this->assertEquals('Product name', $product->display_name);
    }

    public function test_product_has_billable_products()
    {
        $product = Product::factory()->has(BillableProduct::factory()->count(2), 'billables')->create();

        $this->assertCount(2, $product->billables);
    }
}
