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

namespace App\Http\Controllers\Api\Resource;

use Illuminate\Http\Request;
use App\Innoclapps\Facades\Innoclapps;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\GlobalSearch;

class GlobalSearchController extends ApiController
{
    /**
     * Perform global search
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        return $this->response(
            new GlobalSearch(
                Innoclapps::globallySearchableResources()
            )
        );
    }
}
