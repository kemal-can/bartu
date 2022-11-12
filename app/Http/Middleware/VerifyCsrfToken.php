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

use App\Innoclapps\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return boolean
     */
    protected function inExceptArray($request)
    {
        $this->except = $this->getExceptArray();

        return parent::inExceptArray($request);
    }

    /**
     * Get the except array
     *
     * Since the $except property does not allow such operations, we will
     * need to overide the inExceptArray method and perform additional coomparision
     *
     * @return array
     */
    public function getExceptArray()
    {
        return [
            Application::INSTALL_ROUTE_PREFIX . '/*',
            '/forms/f/*',
            '/' . Application::API_PREFIX . '/voip/events',
            '/' . Application::API_PREFIX . '/voip/call',
            '/' . Application::API_PREFIX . '/translation/*/*',
        ];
    }
}
