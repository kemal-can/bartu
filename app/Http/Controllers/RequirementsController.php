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

use App\Installer\EnvironmentManager;
use App\Installer\PermissionsChecker;
use App\Installer\RequirementsChecker;
use App\Installer\FinishesInstallation;

class RequirementsController extends Controller
{
    use FinishesInstallation;

    /**
     * Shows the requirements page
     *
     * @param \App\Installer\RequirementsChecker $requirements
     * @param \App\Installer\PermissionsChecker $permissions
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(RequirementsChecker $requirements, PermissionsChecker $permissions)
    {
        $php            = $requirements->checkPHPversion();
        $requirements   = $requirements->check();
        $permissions    = $permissions->check();
        $memoryLimitMB  = EnvironmentManager::getMemoryLimitInMegabytes();
        $memoryLimitRaw = ini_get('memory_limit');

        $withSteps = false;

        return view('requirements', [
            'withSteps'      => $withSteps,
            'php'            => $php,
            'requirements'   => $requirements,
            'permissions'    => $permissions,
            'memoryLimitMB'  => $memoryLimitMB,
            'memoryLimitRaw' => $memoryLimitRaw,
        ]);
    }

    /**
     * Confirm the requirements
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm()
    {
        $this->captureEnvVariables();

        return redirect()->back();
    }
}
