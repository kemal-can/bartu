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
use App\Http\Controllers\Controller;
use App\Innoclapps\Facades\ReCaptcha;
use Illuminate\Support\Facades\Password;
use App\Innoclapps\Rules\ValidRecaptchaRule;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails {
        SendsPasswordResetEmails::sendResetLinkEmail as mainSendResetLinkEmail;
        SendsPasswordResetEmails::showLinkRequestForm as mainshowLinkRequestForm;
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        if (forgot_password_is_disabled()) {
            abort(404);
        }

        return $this->mainshowLinkRequestForm();
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (forgot_password_is_disabled()) {
            abort(404);
        }

        return $this->mainSendResetLinkEmail($request);
    }

    /**
     * Validate the email for the given request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(array_merge([
            'email' => 'required|email',
        ], ReCaptcha::shouldShow() ? ['g-recaptcha-response' => ['required', new ValidRecaptchaRule]] : []));
    }
}
