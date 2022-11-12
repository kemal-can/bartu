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

namespace App\Innoclapps\Contracts\VoIP;

use Illuminate\Http\Request;
use App\Innoclapps\VoIP\Call;

interface VoIPClient
{
    /**
     * Validate the request for authenticity
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function validateRequest(Request $request);

    /**
     * Create new outgoing call from request
     *
     * @param string $phoneNumber
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function newOutgoingCall($phoneNumber, Request $request);

    /**
     * Create new incoming call from request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function newIncomingCall(Request $request);

    /**
     * Get the Call class from the given webhook request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Innoclapps\VoIP\Call
     */
    public function getCall(Request $request) : Call;
}
