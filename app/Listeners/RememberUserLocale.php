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

namespace App\Listeners;

class RememberUserLocale
{
    /**
     * Remembers  the user default locale in session after
     * the user is logged in.
     *
     * This helps to persist the locale even after the user is logged out
     * In the login page will show the correct locale
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        session()->put('locale', $event->user->preferredLocale());
    }
}
