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
use App\Innoclapps\Concerns\HasMeta;
use App\Innoclapps\Contracts\Metable;
use App\Support\Concerns\UserOrderable;
use App\Innoclapps\Contracts\Primaryable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Support\Concerns\RestrictsModelVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pipeline extends Model implements Metable, Primaryable
{
    use HasMeta, HasFactory, RestrictsModelVisibility, UserOrderable;

    /**
     * Default sort meta key
     */
    const PREFERRED_SORT_META = 'default-sort-user-{userId}';

    /**
     * The flag that indicates it's the primary pipeline
     */
    const PRIMARY_FLAG = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * A pipeline has many deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function deals()
    {
        return $this->hasMany(\App\Models\Deal::class);
    }

    /**
     * A pipeline has many stages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    /**
     * Check whether the pipeline is the primary one
     *
     * @return boolean
     */
    public function isPrimary() : bool
    {
        return $this->flag === static::PRIMARY_FLAG;
    }

    /**
     * Set the pipeline board default sort
     *
     * @param int $userId
     * @param array $data
     *
     * @return static
     */
    public function setDefaultSortData(array $data, int $userId) : static
    {
        $this->setMeta(
            str_replace('{userId}', $userId, static::PREFERRED_SORT_META),
            $data
        );

        return $this;
    }

    /**
     * Get the pipeline board default sort
     *
     * @param int $userId
     * @param array $data
     *
     * @return array|null
     */
    public function getDefaultSortData(int $userId) : ?array
    {
        return $this->getMeta(str_replace('{userId}', $userId, static::PREFERRED_SORT_META));
    }

    /**
     * Get the pipeline defaultSortData attribute for the current user
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function defaultSortData() : Attribute
    {
        return Attribute::get(fn () => $this->getDefaultSortData(auth()->id()));
    }
}
