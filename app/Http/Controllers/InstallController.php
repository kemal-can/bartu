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

use Illuminate\Http\Request;
use App\Installer\Environment;
use App\Innoclapps\Updater\Patcher;
use Illuminate\Support\Facades\URL;
use App\Installer\PrivilegesChecker;
use Illuminate\Support\Facades\Hash;
use App\Installer\EnvironmentManager;
use App\Installer\PermissionsChecker;
use App\Installer\RequirementsChecker;
use App\Installer\FinishesInstallation;
use Illuminate\Support\Facades\Validator;
use App\Contracts\Repositories\UserRepository;
use App\Installer\PrivilegeNotGrantedException;
use App\Innoclapps\Rules\ValidTimezoneCheckRule;
use App\Innoclapps\Updater\Exceptions\UpdaterException;

class InstallController extends Controller
{
    use FinishesInstallation;

    /**
     * Shows the requirements page
     *
     * @param \App\Installer\RequirementsChecker $checker
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(RequirementsChecker $checker)
    {
        $step           = 1;
        $requirements   = $checker->check();
        $php            = $checker->checkPHPversion();
        $memoryLimitMB  = EnvironmentManager::getMemoryLimitInMegabytes();
        $memoryLimitRaw = ini_get('memory_limit');

        return view('installer.requirements', compact(
            'step',
            'php',
            'requirements',
            'memoryLimitMB',
            'memoryLimitRaw'
        ));
    }

    /**
     * Shows the permissions page
     *
     * @param \App\Installer\PermissionsChecker $checker
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function permissions(PermissionsChecker $checker)
    {
        $step        = 2;
        $permissions = $checker->check();

        return view('installer.permissions', compact('step', 'permissions'));
    }

    /**
     * Application setup
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function setup()
    {
        $step = 3;

        $guessedUrl = EnvironmentManager::guessUrl();

        $countries  = \Countries::getList();
        $currencies = config('money');

        return view('installer.setup', compact(
            'step',
            'guessedUrl',
            'countries',
            'currencies'
        ));
    }

    /**
     * Store the environmental variables
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Installer\EnvironmentManager $environmentManager
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setupStore(Request $request, EnvironmentManager $environmentManager)
    {
        $validator = Validator::make($request->all(), [
            'app_url'           => 'required|url',
            'app_name'          => 'required',
            'currency'          => 'required',
            'country'           => 'required',
            'database_hostname' => 'required',
            'database_port'     => 'required',
            'database_name'     => 'required',
            'database_username' => 'required',
            // Allow blank for local installs
            // 'database_password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('install/setup')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $connection = $this->testDatabaseConnection($request);
            (new PrivilegesChecker($connection))->check();
        } catch (\Exception $e) {
            $this->setDatabaseTestsErrors($validator, $e);

            return redirect('install/setup')
                ->withErrors($validator)
                ->withInput();
        }

        if (! $environmentManager->saveEnvFile(new Environment(
            name: $request->app_name,
            key: config('app.key'),
            identificationKey: config('innoclapps.key'),
            url: $request->app_url,
            dbHost: $request->database_hostname,
            dbPort: $request->database_port,
            dbName: $request->database_name,
            dbUser: $request->database_username,
            dbPassword: $request->database_password ?: '',
        ))) {
            return redirect('install/setup')
                ->withErrors([
                    'general' => 'Failed to write .env file, make sure that the files permissions and ownership are correct. Check documentation on how to setup the permissions and ownership.',
                ]);
        }

        session(['install_country' => $request->country, 'install_currency' => $request->currency]);

        // Use the request app_url parameter as the user may have changed
        // the url and will have different value in the .env file
        return redirect(rtrim($request->app_url, '/') . '/install/database');
    }

    /**
     * Migrate the database
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function database()
    {
        ini_set('memory_limit', '256M');

        $this->migrate();

        return redirect('install/user');
    }

    /**
     * Display the user step
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function user()
    {
        $step = 4;

        return view('installer.user', compact('step'));
    }

    /**
     * Store the user
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\Repositories\UserRepository $repository
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userStore(Request $request, UserRepository $repository)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:191',
            'email'    => 'required|string|email|max:191|unique:users',
            'timezone' => ['required', new ValidTimezoneCheckRule],
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect('install/user')
                ->withErrors($validator)
                ->withInput();
        }

        $repository->createViaInstall([
            'name'     => $request->name,
            'email'    => $request->email,
            'timezone' => $request->timezone,
            'password' => Hash::make($request->password),
        ]);

        return redirect('install/finalize');
    }

    /**
     * Display the finish step
     *
     * @param \App\Contracts\Repositories\UserRepository $repository
     * @param \App\Innoclapps\Updater\Patcher $patcher
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function finished(UserRepository $repository, Patcher $patcher)
    {
        $step = 5;
        $user = $repository->first();

        if ((new RequirementsChecker)->passes('zip')) {
            try {
                $patches = $patcher->getAvailablePatches()->reject->isApplied();
            } catch (\Exception $e) {
                // Do nothing if any exception is thrown
            }
        }

        return view('installer.finish', [
            'step'          => $step,
            'user'          => $user,
            'patches'       => $patches ?? [],
            'phpExecutable' => \App\Innoclapps\Application::getPhpExecutablePath(),
        ]);
    }

    /**
     * Finalize the installation with redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalize()
    {
        $errors = $this->finalizeInstallation(
            session()->pull('install_currency', settings('currency')),
            session()->pull('install_country', settings('company_country_id'))
        );

        $route = URL::temporarySignedRoute('install.finished', now()->addMinutes(60));

        return redirect($route)->withErrors($errors);
    }

    /**
     * Apply the available patches
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function patch(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'purchase_key' => 'required',
        ]);

        $backWithErrors = function ($validator) {
            return back()->withErrors($validator)->withInput();
        };

        if ($validator->fails()) {
            return $backWithErrors($validator);
        }

        settings(['purchase_key' => $request->purchase_key]);

        // Resolve after setting the purchase key it reflects the config
        $patcher = app(Patcher::class);

        try {
            $patcher->getAvailablePatches()->reject->isApplied()
                ->each(function ($patch) use ($patcher) {
                    $patcher->apply($patch->token());
                });
        } catch (UpdaterException $e) {
            $validator->getMessageBag()->add('general', $e->getMessage());

            return $backWithErrors($validator);
        }

        return back();
    }

    /**
     * Set the database tests errors
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param \Exception $e
     *
     * @return void
     */
    protected function setDatabaseTestsErrors($validator, $e)
    {
        // https://stackoverflow.com/questions/41835923/syntax-error-or-access-violation-1115-unknown-character-set-utf8mb4
        if (strstr($e->getMessage(), 'Unknown character set')) {
            $validator->getMessageBag()->add('general', 'At least MySQL 5.6 version is required.');
        } elseif ($e instanceof PrivilegeNotGrantedException) {
            $validator->getMessageBag()->add('privilege', 'The ' . $e->getPriviligeName() . ' privilige is not granted to the database user, the following error occured during tests: ' . $e->getMessage());
        } else {
            $validator->getMessageBag()->add('general', 'Could not establish database connection: ' . $e->getMessage());
            $validator->getMessageBag()->add('database_hostname', 'Please check entered value.');
            $validator->getMessageBag()->add('database_port', 'Please check entered value.');
            $validator->getMessageBag()->add('database_name', 'Please check entered value.');
            $validator->getMessageBag()->add('database_username', 'Please check entered value.');
            $validator->getMessageBag()->add('database_password', 'Please check entered value.');
        }
    }

    /**
     * Test the database connection
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Connection
     */
    protected function testDatabaseConnection($request)
    {
        $params = [
            'driver'    => 'mysql',
            'host'      => $request->database_hostname,
            'database'  => $request->database_name,
            'username'  => $request->database_username,
            'password'  => $request->database_password,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ];

        $hash = md5(json_encode($params));

        \Config::set('database.connections.install' . $hash, $params);

        /**
         * @var \Illuminate\Database\Connection
         */
        $connection = \DB::connection('install' . $hash);

        // Triggers PDO init, in case of errors, will fail and throw exception
        $connection->getPdo();

        return $connection;
    }
}
