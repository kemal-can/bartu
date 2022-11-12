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

namespace App\Http\Controllers\Api\Twilio;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Twilio\Exceptions\RestException;
use App\Http\Controllers\ApiController;

class TwilioAppController extends ApiController
{
    /**
     * Get TwiML Application
     *
     * @param string $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $request->validate([
            'account_sid' => Rule::when(! $this->accountSid(), 'required'),
            'auth_token'  => Rule::when(! $this->authToken(), 'required'),
        ]);

        return $this->response(
            $this->createClient($request)->applications($id)->fetch()->toArray()
        );
    }

    /**
     * Update TwiML Application
     *
     * @param string $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'account_sid' => Rule::when(! $this->accountSid(), 'required'),
            'auth_token'  => Rule::when(! $this->authToken(), 'required'),
        ]);

        return $this->response(
            $this->createClient($request)->applications($id)->update($request->all())
        );
    }

    /**
     * Create new TwiML Application
     *
     * @see https://www.twilio.com/docs/phone-numbers/api/incomingphonenumber-resource?code-sample=code-fetch-incoming-phone-number&code-language=PHP&code-sdk-version=6.x
     * @see  https://support.twilio.com/hc/en-us/articles/223135027-Configure-a-Twilio-Phone-Number-to-Receive-and-Respond-to-Voice-Calls
     * @see  https://www.twilio.com/docs/usage/api/applications#list-post-example-1
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'number'      => 'required',
            'account_sid' => Rule::when(! $this->accountSid(), 'required'),
            'auth_token'  => Rule::when(! $this->authToken(), 'required'),
        ]);

        $client = $this->createClient($request);

        $incomingPhoneNumbers = $client->incomingPhoneNumbers
            ->read(['phoneNumber' => $data['number']], 1);

        if (count($incomingPhoneNumbers) === 0) {
            abort(409, 'Incoming phone number not found.');
        }

        $number = $incomingPhoneNumbers[0];

        if ($number->capabilities['voice'] === false) {
            abort(409, 'This phone number does not have enabled voice capabilities.');
        }

        $application = $client->applications->create(
            $request->except(['number', 'account_sid', 'auth_token'])
        );

        $client->incomingPhoneNumbers($number->sid)->update([
            'voiceApplicationSid' => $application->sid,
        ]);

        return $this->response([
            'app_sid' => $application->sid,
        ], 201);
    }

    /**
     * Delete TwiML Application
     *
     * @param string $sid
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($sid, Request $request)
    {
        $request->validate([
            'account_sid' => Rule::when(! $this->accountSid(), 'required'),
            'auth_token'  => Rule::when(! $this->authToken(), 'required'),
        ]);

        try {
            $this->createClient($request)->applications($sid)->delete();
            settings()->forget('twilio_app_sid')->save();
        } catch (RestException $e) {
            if ($e->getStatusCode() !== 404) {
                throw $e;
            }

            settings()->forget('twilio_app_sid')->save();
        }

        return $this->response('', 204);
    }

    /**
     * Create new Twilio Client from the given request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Twilio\Rest\Client
     */
    protected function createClient($request)
    {
        return new Client(
            $request->input('account_sid', $this->accountSid()),
            $request->input('auth_token', $this->authToken())
        );
    }

    /**
     * Get Twilio account SID
     *
     * @return string|null
     */
    protected function accountSid()
    {
        return config('innoclapps.services.twilio.accountSid');
    }

    /**
     * Get Twilio auth token
     *
     * @return string|null
     */
    protected function authToken()
    {
        return config('innoclapps.services.twilio.authToken');
    }
}
