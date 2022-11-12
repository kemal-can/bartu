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

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Innoclapps\Facades\ReCaptcha;
use App\Providers\RouteServiceProvider;
use App\Innoclapps\Rules\ValidRecaptchaRule;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate the user login request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate(array_merge([
            $this->username() => 'required|string',
            'password'        => 'required|string',
        ], ReCaptcha::shouldShow() ? ['g-recaptcha-response' => ['required', new ValidRecaptchaRule]] : []));
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function authenticated(Request $request, $user)
    {
        return $request->wantsJson()
        ? new JsonResponse([
            'redirect_path' => redirect()->intended($this->redirectPath())->getTargetUrl(),
        ], 200)
        : redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        // Get the current stored locale via the RememberUserLocale listener
        $currentLocale = $request->session()->pull('locale');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Store again the locale as the session is flushed via the invalidate method
        $request->session()->put('locale', $currentLocale);

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect('/');
    }
}
