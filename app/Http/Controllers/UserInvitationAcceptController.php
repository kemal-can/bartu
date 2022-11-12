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

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Translation\Translation;
use App\Contracts\Repositories\UserRepository;
use App\Innoclapps\Rules\ValidTimezoneCheckRule;
use App\Contracts\Repositories\UserInvitationRepository;

class UserInvitationAcceptController extends Controller
{
    /**
     * Initialize new UserInvitationAcceptController instance.
     *
     * @param \App\Contracts\Repositories\UserInvitationRepository $repository
     */
    public function __construct(protected UserInvitationRepository $repository)
    {
    }

    /**
     * Show to invitation accept form
     *
     * @param string $token
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show($token, Request $request)
    {
        $invitation = $this->repository->findByToken($token);
        $this->authorizeInvitation($invitation);

        return view('invitations.show', compact('invitation'));
    }

    /**
     * Accept the invitation and create account
     *
     * @param string $token
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\UserRepository $users
     *
     * @return void
     */
    public function accept($token, Request $request, UserRepository $users)
    {
        $invitation = $this->repository->findByToken($token);

        $this->authorizeInvitation($invitation);

        $data = $request->validate([
            'name'              => 'required|string|max:191',
            'email'             => 'email|max:191|unique:' . (new User())->getTable(),
            'password'          => 'required|confirmed|min:6',
            'timezone'          => ['required', new ValidTimezoneCheckRule],
            'locale'            => ['nullable', Rule::in(Translation::availableLocales())],
            'date_format'       => ['required', Rule::in(config('app.date_formats'))],
            'time_format'       => ['required', Rule::in(config('app.time_formats'))],
            'first_day_of_week' => 'required|in:1,6,0',
        ]);

        $user = $users->create(array_merge($data, [
            'super_admin'   => $invitation->super_admin,
            'access_api'    => $invitation->access_api,
            'roles'         => $invitation->roles,
            'teams'         => $invitation->teams,
            'email'         => $invitation->email,
            'notifications' => collect(Innoclapps::notificationsInformation())->mapWithKeys(function ($setting) {
                return [$setting['key'] => $setting['channels']->mapWithKeys(function ($channel) {
                    return [$channel => true];
                })->all()];
            })->all(),
        ]));

        Auth::loginUsingId($user->id);

        $this->repository->delete($invitation->id);
    }

    /**
     * Authorize the given invitation
     *
     * @param \App\Models\UserInvitation|null $invitation
     *
     * @return void
     */
    protected function authorizeInvitation($invitation)
    {
        abort_if(is_null($invitation)
                || $invitation->created_at->diffInDays() > config('app.invitation.expires_after'), 404);
    }
}
