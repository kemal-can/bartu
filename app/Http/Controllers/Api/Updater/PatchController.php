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

use App\Innoclapps\Updater\Patcher;
use App\Installer\RequirementsChecker;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Updater\Exceptions\UpdaterException;

class PatchController extends ApiController
{
    /**
     * Get the available patches for the installed version
     *
     * @param \App\Innoclapps\Updater\Patcher $patcher
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Patcher $patcher)
    {
        return $this->response($patcher->getAvailablePatches());
    }

    /**
     * Apply the given patch to the current installed version
     *
     * @param string $token
     * @param string|null $purchaseKey
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply($token, $purchaseKey = null)
    {
        // Apply patch flag

        $patcher = app(Patcher::class);

        if (! empty($purchaseKey)) {
            settings(['purchase_key' => $purchaseKey]);
        }

        abort_unless(
            (new RequirementsChecker)->passes('zip'),
            409,
            __('update.patch_zip_is_required')
        );

        $patch = $patcher->usePurchaseKey($purchaseKey ?: '')->find($token);

        if ($patch->isApplied()) {
            throw new UpdaterException('This patch is already applied.', 409);
        }

        $patcher->apply($patch);

        return $this->response('', 204);
    }
}
