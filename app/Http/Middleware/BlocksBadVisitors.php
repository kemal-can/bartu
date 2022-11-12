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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Innoclapps\Facades\Innoclapps;

class BlocksBadVisitors
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
        if ($this->shouldSkip()) {
            return $next($request);
        }

        if (in_array($request->userAgent(), $this->getBadUserAgents())) {
            abort(403);
        }

        $referer = $request->headers->get('referer') ?: null;

        if ($referer && in_array($referer, $this->getBadReferrers())) {
            abort(403);
        }

        if (in_array($request->ips(), $this->getBadIps())) {
            abort(403);
        }

        return $next($request);
    }

    /**
     * Check whether the checks should be skipped
     *
     * @return boolean
     */
    protected function shouldSkip() : bool
    {
        return Auth::check() || settings('block_bad_visitors') === false || app()->runningUnitTests() || ! Innoclapps::isAppInstalled();
    }

    /**
     * Get bad referrers
     *
     * @return array
     */
    protected function getBadReferrers() : array
    {
        return $this->getList('bad-referrers');
    }

    /**
     * Get bad ips
     *
     * @return array
     */
    protected function getBadIps() : array
    {
        return $this->getList('bad-ip-addresses');
    }

    /**
     * Get bad user agents
     *
     * @return array
     */
    protected function getBadUserAgents() : array
    {
        return $this->getList('bad-user-agents');
    }

    /**
     * Get list
     *
     * @param string $type
     *
     * @return array
     */
    protected function getList(string $type) : array
    {
        return Cache::remember('bv-' . $type, now()->addDay(1), function () use ($type) {
            $response = Http::get(
                'https://raw.githubusercontent.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker/master/_generator_lists/' . $type . '.list'
            );

            if ($response->successful()) {
                return  explode("\n", trim($response->body()));
            }

            return [];
        });
    }
}
