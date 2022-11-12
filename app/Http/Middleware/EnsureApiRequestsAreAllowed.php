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

class EnsureApiRequestsAreAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() &&
            $request->bearerToken() &&
            $request->user()->cant('access-api')) {
            return response()->json([
                'error' => 'Your account is not authorized to perform API requests.',
            ], 403);
        }

        return $next($request);
    }
}
