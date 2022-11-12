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
use App\Innoclapps\Updater\Migration;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Contracts\Foundation\Application;

class PreventRequestsWhenMigrationNeeded
{
    /**
     * The URIs that should be accessible even when migration is needed.
     *
     * @var array
     */
    protected $except = [
        '/errors/migration',
        '/api/tools/migrate',
    ];

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

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
        // TODO, perhaps add the unit tests migration paths to the checker
        if (! $this->app->runningUnitTests() &&
       $this->isSuperAdmin($request) &&
       Innoclapps::migrationNeeded()) {
            if (! $this->inExceptArray($request)) {
                return redirect('/errors/migration', 302);
            }
        }

        return $next($request);
    }

    /**
     * Check whether the user is super admin
     *
     * @return bool
     */
    protected function isSuperAdmin($request)
    {
        return $request->user() && $request->user()->isSuperAdmin();
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->getExcludedPaths() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the URIs that should be accessible even when maintenance mode is enabled.
     *
     * @return array
     */
    public function getExcludedPaths()
    {
        return $this->except;
    }
}
