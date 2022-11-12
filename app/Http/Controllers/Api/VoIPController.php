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

use Illuminate\Http\Request;
use App\Innoclapps\Facades\VoIP;
use App\Http\Controllers\ApiController;
use App\Innoclapps\VoIP\Events\IncomingCallMissed;

class VoIPController extends ApiController
{
    /**
     * Call events
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function events(Request $request)
    {
        VoIP::validateRequest($request);

        $call = VoIP::getCall($request);

        if ($call->isMissedIncoming()) {
            event(new IncomingCallMissed($call));
        }

        return VoIP::events($request);
    }

    /**
     * Initiate new call
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function newCall(Request $request)
    {
        VoIP::validateRequest($request);

        if (VoIP::shouldReceivesEvents()) {
            VoIP::setEventsUrl(VoIP::eventsUrl());
        }

        if ($request->has('viaApp')) {
            return VoIP::newOutgoingCall(
                $request->input('To'),
                $request
            );
        }

        return VoIP::newIncomingCall($request);
    }

    /**
     * Create a new client token
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function newToken(Request $request)
    {
        return $this->response(['token' => VoIP::newToken($request)]);
    }
}
