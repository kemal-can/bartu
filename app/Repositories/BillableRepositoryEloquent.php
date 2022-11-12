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

namespace App\Repositories;

use App\Enums\TaxType;
use App\Models\Billable;
use Illuminate\Support\Arr;
use App\Models\BillableProduct;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Criteria\WithTrashedCriteria;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\BillableRepository;
use App\Contracts\Repositories\BillableProductRepository;

class BillableRepositoryEloquent extends AppRepository implements BillableRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Billable::class;
    }

    /**
     * Save the billable data in storage
     *
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $billableable
     *
     * @return \App\Models\Billable
     */
    public function save(array $data, $billableable)
    {
        $productRepository = resolve(ProductRepository::class);
        $products          = $this->setProductsDefaults($data['products'] ?? []);
        $billable          = $billableable->billable()->firstOrNew();

        $billable->fill(array_merge([
            'note'  => null,
            'terms' => null,
        ], with($this->determineTaxType($data, $billable->exists), function ($taxType) {
            return $taxType !== false ? ['tax_type' => $taxType] : [];
        })))->save();

        foreach ($products as $line) {
            // Update existing product
            if (! empty($line['id'] ?? null)) {
                // Get and remove the id from the line so
                // it won't be passed to the firstOrCreate method
                $id = Arr::pull($line, 'id');

                // When user enter new product on existing product selected
                $product = $productRepository
                    ->pushCriteria(WithTrashedCriteria::class)
                    ->firstOrCreate(
                        ['name' => $line['name']],
                        array_merge($line, ['is_active' => true])
                    );

                if ($product->trashed()) {
                    $productRepository->restore($product);
                }

                $billable->products()->find($id)->fill(
                    // In case new product created, update the product_id attribute
                    array_merge($line, ['product_id' => $product->getKey()])
                )->save();

                continue;
            }

            // Handle new products creation
            if (! isset($line['product_id'])) {
                // In case the product exists with a given name use the existing product instead
                $product = $productRepository->pushCriteria(WithTrashedCriteria::class)
                    ->firstOrCreate(
                        ['name' => $line['name']],
                        array_merge($line, ['is_active' => true])
                    );

                if ($product->trashed()) {
                    $productRepository->restore($product);
                }

                $billable->products()->create(
                    array_merge($line, ['product_id' => $product->getKey()])
                );

                continue;
            }

            // Regularly product selected from dropdown
            $billable->products()->create($line);
        }

        if (count($data['removed_products'] ?? []) > 0) {
            $this->removeProducts($data['removed_products']);
        }

        return tap($billable, function ($instance) {
            $this->updateTotalBillableableColumn($instance);
        });
    }

    /**
     * Determine the billable tax type
     *
     * @param array $data
     * @param boolean $exists
     *
     * @return false|\App\Enums\TaxType
     */
    protected function determineTaxType($data, $exists)
    {
        $taxType = false;

        if ($exists && isset($data['tax_type']) && ! empty($data['tax_type'])) {
            $taxType = $data['tax_type'];
        } elseif (! $exists) {
            $taxType = empty($data['tax_type'] ?? null) ? Billable::defaultTaxType() : $data['tax_type'];
        }

        if (is_string($taxType)) {
            $taxType = TaxType::find($taxType);
        }

        return $taxType;
    }

    /**
     * Set the products defaults
     *
     * @param array $products
     *
     * @return array
     */
    protected function setProductsDefaults(array $products) : array
    {
        $repository = app(ProductRepository::class);

        foreach ($products as $index => $line) {
            $products[$index] = array_merge($line, [
                'display_order' => $line['display_order'] ?? $index + 1,
                'discount_type' => $line['discount_type'] ?? BillableProduct::defaultDiscountType(),
                'tax_label'     => $line['tax_label'] ?? BillableProduct::defaultTaxLabel(),
                'tax_rate'      => $line['tax_rate'] ?? BillableProduct::defaultTaxRate(),
            ]);

            // When the product name is not set and the product_id exists
            // we will use the name from the actual product_id, useful when creating products via Zapier
            if (isset($line['product_id']) && ! isset($line['name'])) {
                $products[$index]['name'] = $repository->find($line['product_id'])->name;
            }
        }

        return $products;
    }

    /**
     * Remove the given products id's from the products billable
     *
     * @param array $products
     *
     * @return void
     */
    public function removeProducts(array $products)
    {
        resolve(BillableProductRepository::class)->findWhereIn('id', $products)->each->delete();
    }

    /**
     * Update the billable billableable total column (if using)
     *
     * @param \App\Models\Billable $billable
     *
     * @return void
     */
    protected function updateTotalBillableableColumn($billable)
    {
        if ($totalColumn = $billable->billableable->totalColumn()) {
            $billable->billableable->{$totalColumn} = $billable->total;
            $billable->billableable->save();
        }
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(fn ($model) => $model->products->each->delete());
    }
}
