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

class PrivacyPolicy extends Controller
{
    /**
     * Privacy Policy
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke()
    {
        $content = clean(settings('privacy_policy'));

        return view('privacy-policy', compact('content'));
    }
}
