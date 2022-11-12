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

namespace App\Http\Controllers;

use App\Innoclapps\Updater\Patcher;

class UpdateDownloadController extends Controller
{
    /**
     * Download the given patch
     *
     * @param string $token
     * @param string|null $purchaseKey
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPatch($token, $purchaseKey = null)
    {
        // Download patch flag

        if ($purchaseKey) {
            settings(['purchase_key' => $purchaseKey]);
        }

        $patcher = app(Patcher::class);

        return $patcher->download($token);
    }
}
