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

namespace App\Http\Controllers\Api;

use App\Innoclapps\Facades\Cards;
use App\Http\Controllers\ApiController;

class CardController extends ApiController
{
    /**
     * Get cards that are intended to be shown on dashboards
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forDashboards()
    {
        return $this->response(Cards::resolveForDashboard());
    }

    /**
     * Get the available cards for a given resource
     *
     * @param string $resourceName
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($resourceName)
    {
        return $this->response(Cards::resolve($resourceName));
    }

    /**
     * Get card by given uri key
     *
     * @param string $card
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($card)
    {
        return $this->response(Cards::registered()->first(function ($item) use ($card) {
            return $item->uriKey() === $card;
        })->authorizeOrFail());
    }
}
