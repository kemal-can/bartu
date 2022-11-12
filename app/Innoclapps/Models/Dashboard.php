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

namespace App\Innoclapps\Models;

use App\Innoclapps\Facades\Cards;

class Dashboard extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cards'      => 'array',
        'is_default' => 'boolean',
        'user_id'    => 'int',
    ];

    /**
     * Get the default available dashboard cards
     *
     * @param \App\Models\User|null $user
     *
     * @return \Illuminate\Support\Collection
     */
    public static function defaultCards($user = null)
    {
        return Cards::registered()->filter->authorizedToSee($user)
            ->reject(fn ($card) => $card->onlyOnIndex === true)
            ->values()
            ->map(function ($card, $index) {
                return ['key' => $card->uriKey(), 'order' => $index + 1];
            });
    }
}
