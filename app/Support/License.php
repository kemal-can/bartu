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

namespace App\Support;

use Illuminate\Support\Facades\Http;

class License
{
    /**
     * Verify the given license
     *
     * @param string $key
     *
     * @return boolean
     */
    public static function verify($key)
    {
        $response = Http::withHeaders([
            'X-bartu-Installation' => true,
        ])
            ->contentType('application/json')
            ->get('https://www.bartucrm.com/verify-license/' . $key);

        if ($response->successful()) {
            return $response['valid'];
        }

        return false;
    }
}
