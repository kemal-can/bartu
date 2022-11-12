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

use App\Innoclapps\Updater\Migration;
use App\Innoclapps\Facades\Innoclapps;

class MigrationRequired extends Controller
{
    /**
     * Show the migration required action
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke()
    {
        abort_unless(Innoclapps::migrationNeeded(), 404);

        return view('migrate');
    }
}
