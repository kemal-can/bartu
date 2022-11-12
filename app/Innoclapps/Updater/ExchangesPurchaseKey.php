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

namespace App\Innoclapps\Updater;

trait ExchangesPurchaseKey
{
    /**
     * @var string|null
     */
    protected ?string $purchaseKey = null;

    /**
     * Use the given custom purchase key
     *
     * @param string $key
     *
     * @return static
     */
    public function usePurchaseKey(string $key) : static
    {
        $this->purchaseKey = $key;

        return $this;
    }

    /**
     * Get the updater purchase key
     *
     * @return string|null
     */
    public function getPurchaseKey()
    {
        return $this->purchaseKey ?: $this->config['purchase_key'];
    }
}
