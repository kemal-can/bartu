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
use App\Innoclapps\Translation\Translation;

class ServeLocalizedApplication
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($locale = $this->determineLocale($request)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function determineLocale($request)
    {
        $locale = $this->determineUserLocale($request);

        if (is_null($locale)) {
            // User not logged in, try to determine the locale from the request
            $locale = $request->getPreferredLanguage(
                Translation::availableLocales()
            );
        }

        if (Translation::localeExist($locale)) {
            return $locale;
        }
    }

    /**
     * Determine the user locale
     *
     * @param \Illuminate\Http\Reqeuest $request
     *
     * @return string|null
     */
    protected function determineUserLocale($request)
    {
        // Check if there is a user in the request, if so,
        // we will retireve the locale from the user preferred locale
        if ($request->user()) {
            return $request->user()->preferredLocale();
        } elseif (! $request->is(\App\Innoclapps\Application::API_PREFIX . '/*') && $request->session()->has('locale')) {
            // Usually used when initializing the application or after the user is logged out
            return $request->session()->get('locale');
        }
    }
}
