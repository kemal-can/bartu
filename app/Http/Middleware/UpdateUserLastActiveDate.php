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
use Illuminate\Support\Facades\Auth;
use App\Contracts\Repositories\UserRepository;

class UpdateUserLastActiveDate
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
        return tap($next($request), function () use ($request) {
            if (Auth::check()) {
                resolve(UserRepository::class)->unguarded(function ($repository) use ($request) {
                    $repository->update(['last_active_at' => now()], $request->user()->getKey());
                });
            }
        });
    }
}
