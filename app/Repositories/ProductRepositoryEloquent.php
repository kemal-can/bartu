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

use App\Models\Product;
use App\Innoclapps\Repository\SoftDeletes;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\BillableProductRepository;

class ProductRepositoryEloquent extends AppRepository implements ProductRepository
{
    use SoftDeletes;

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'sku',
        'is_active',
        'name' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Product::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                resolve(BillableProductRepository::class)->massUpdate(
                    ['product_id' => null],
                    ['product_id' => $model->getKey()]
                );
            }
        });
    }
}
