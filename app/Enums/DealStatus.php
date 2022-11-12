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

namespace App\Enums;

enum DealStatus : int {

    use InteractsWithEnums;

    case open = 1;
    case won  = 2;
    case lost = 3;

    /**
     * Get the deal status badge variant.
     *
     * @return string
     */
    public function badgeVariant() : string
    {
        return static::badgeVariants()[$this->name];
    }

    /**
     * Get the available badge variants.
     *
     * @return array
     */
    public static function badgeVariants() : array
    {
        return [
            DealStatus::open->name => 'neutral',
            DealStatus::won->name  => 'success',
            DealStatus::lost->name => 'danger',
        ];
    }
}
