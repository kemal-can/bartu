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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class LogoController extends ApiController
{
    /**
     * Save company logo
     *
     * @param string $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($type, Request $request)
    {
        // Logo store flag

        $logoName = 'logo_' . $type;

        $request->validate([
            $logoName => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);

        $this->destroy($type);

        if ($filename = $request->file($logoName)->store('/', 'public')) {
            $logoUrl = "/storage/$filename";
            settings([$logoName => $logoUrl]);

            return $this->response(['logo' => $logoUrl]);
        }

        abort(500, 'Failed to save the logo.');
    }

    /**
     * Remove company logo
     *
     * @param string $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($type)
    {
        $optionName  = 'logo_' . $type;
        $currentLogo = settings($optionName);
        $filename    = basename($currentLogo);

        if (! empty($currentLogo) && $this->disk()->exists($filename)) {
            $this->disk()->delete($filename);
        }

        settings()->forget($optionName)->save();

        return $this->response('', 204);
    }

    /**
     * Get the logo storage disk
     *
     * @return mixed
     */
    protected function disk()
    {
        return Storage::disk('public');
    }
}
