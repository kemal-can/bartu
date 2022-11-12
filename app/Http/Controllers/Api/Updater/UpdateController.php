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

namespace App\Http\Controllers\Api\Updater;

use App\Innoclapps\Updater\Updater;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends ApiController
{
    /**
     * Get information about update
     *
     * @param \App\Innoclapps\Updater\Updater $updater
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Updater $updater)
    {
        return $this->response([
            'installed_version'        => $updater->getVersionInstalled(),
            'latest_available_version' => $updater->getVersionAvailable(),
            'is_new_version_available' => $updater->isNewVersionAvailable(),
            'purchase_key'             => $updater->getPurchaseKey(),
        ]);
    }

    /**
     * Perform an application update
     *
     * @param string|null $purchaseKey
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($purchaseKey = null)
    {
        // Update flag

        // Save the purchase key for future usage
        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        Artisan::call('bartu:update', [
            '--key' => $purchaseKey,
        ]);

        return $this->response('', 204);
    }
}
