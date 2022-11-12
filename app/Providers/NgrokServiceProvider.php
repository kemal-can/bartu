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

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 */
class NgrokServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            $urlGenerator = $this->app->make('url');
            $request      = $this->app->make('request');

            $this->forceNgrokSchemeHost($urlGenerator, $request);
        }
    }

    /**
     * Force the url generator to the ngrok scheme://host.
     *
     * @param \Illuminate\Routing\UrlGenerator $urlGenerator
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function forceNgrokSchemeHost($urlGenerator, $request) : void
    {
        $host = $this->extractOriginalHost($request);

        if ($this->isNgrokHost($host)) {
            $scheme = $this->extractOriginalScheme($request);

            $urlGenerator->forceScheme($scheme);
            $urlGenerator->forceRootUrl($scheme . '://' . $host);

            Paginator::currentPathResolver(fn () => $urlGenerator->to($request->path()));
        }
    }

    /**
     * Extract the original scheme from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function extractOriginalScheme($request) : string
    {
        if ($request->hasHeader('x-forwarded-proto')) {
            $scheme = $request->header('x-forwarded-proto');
        } else {
            $scheme = $request->getScheme();
        }

        return $scheme;
    }

    /**
     * Extract the original host from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function extractOriginalHost($request) : string
    {
        if ($request->hasHeader('x-original-host')) {
            $host = $request->header('x-original-host');
        } else {
            $host = $request->getHost();
        }

        return $host;
    }

    /**
     * Check if the host from ngrok.
     *
     * @param string $host
     *
     * @return bool
     */
    protected function isNgrokHost(string $host) : bool
    {
        return preg_match('/^[a-z0-9-]+\.ngrok\.io$/i', $host);
    }
}
