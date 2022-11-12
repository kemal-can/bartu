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

namespace App\Http\Controllers\Api\Calendar;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CalendarResource;
use App\Innoclapps\Calendar\CalendarManager;
use App\Contracts\Repositories\CalendarRepository;
use App\Innoclapps\OAuth\EmptyRefreshTokenException;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class CalendarOAuthController extends ApiController
{
    /**
     * Initialize new CalendarOAuthController instance
     *
     * @param \App\Contracts\Repositories\CalendarRepository $repository
     */
    public function __construct(protected CalendarRepository $repository)
    {
    }

    /**
     * Get the user connected OAuth calendar
     *
     * @param \Illuminate\Http\Request $requst
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $calendar = $this->repository->forUser($request->user());

        return $calendar ? $this->response(
            new CalendarResource($calendar->load('oAuthAccount'))
        ) : null;
    }

    /**
     * Get the available calendars for the given OAuth Account
     *
     * @param int $id
     * @param \App\Innoclapps\Contracts\Repositories\OAuthAccountRepository $repository
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendars($id, OAuthAccountRepository $repository)
    {
        $account = $repository->find($id);

        $connectionType = match ($account->type) {
            'microsoft' => 'outlook',
            default     => $account->type,
        };

        $this->authorize('view', $account);

        try {
            return $this->response(CalendarManager::createClient(
                $connectionType,
                $account->tokenProvider()
            )->getCalendars());
        } catch (EmptyRefreshTokenException $e) {
            abort(500, 'The calendars cannot be retrieved from the ' . $account->email . ' account because the account has empty refresh token, try to remove the app from your ' . explode('@', $account->email)[1] . ' account connected apps section and re-connect again.');
        }
    }

    /**
     * Connect the user connected OAuth account
     *
     * @param \Illuminate\Http\Request $requst
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $payload = $request->validate([
            'calendar_id'      => 'required|string',
            'activity_type_id' => 'required|numeric',
            'access_token_id'  => 'required|numeric',
            'activity_types'   => 'required|array',
        ]);

        $calendar = $this->repository->save($payload, $request->user());

        return $this->response(new CalendarResource($calendar));
    }

    /**
     * Stop the sync for the connected calendar account
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $calendar = $this->repository->forUser($request->user());

        abort_unless($calendar, 404);

        try {
            $this->repository->disableSync($calendar);
        } catch (EmptyRefreshTokenException $e) {
        }


        return $this->response(new CalendarResource(
            $this->repository->with('oAuthAccount')->find($calendar->getKey())
        ));
    }
}
