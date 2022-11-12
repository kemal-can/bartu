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

namespace App\Http\Middleware;

use Closure;
use App\Innoclapps\Facades\Innoclapps;

class PreventInstallationWhenInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle($request, Closure $next)
    {
        if (\App::isProduction()) {
            if ($request->route()->getName() === 'install.finished') {
                /**
                 * Uses signed URL Laravel feature as when the installation
                 * is finished the installed file will be created and if this action
                 * is in the PreventInstallationWhenInstalled middleware, it will show 404 error as the installed
                 * file will exists but we need to show the user that the installation is finished
                 */
                if (! $request->hasValidSignature()) {
                    abort(401);
                }
            } elseif (Innoclapps::isAppInstalled()) {
                abort(404);
            }
        }

        return $next($request);
    }
}
