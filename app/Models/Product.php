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

use App\Resources\Prunable;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Concerns\HasCreator;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements Presentable
{
    use HasCreator,
        HasFactory,
        Resourceable,
        SoftDeletes,
        Prunable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_by',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'is_active'   => 'boolean',
        'unit_price'  => 'decimal:3',
        'direct_cost' => 'decimal:3',
        'tax_rate'    => 'decimal:3',
        'created_by'  => 'int',
    ];

    /**
     * Get the product billable products
     */
    public function billables()
    {
        return $this->hasMany(\App\Models\BillableProduct::class, 'product_id');
    }

    /**
     * Get the model display name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function displayName() : Attribute
    {
        return Attribute::get(fn () => $this->name);
    }

    /**
     * Get the URL path
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::get(fn () => "/products/{$this->id}");
    }
}
