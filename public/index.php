<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/
if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Check PHP Version requirements.
|--------------------------------------------------------------------------
|
| It's important first to check the minimum required PHP version to prevent any
| errors thrown without the user figuring that the issue is related to the
| actual PHP version.
|
*/
if (! version_compare(phpversion(), '8.1', '>=')) {
    die('<h1>At least PHP 8.1 is required to run the application.</h1>');
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Perform Pre Install Checks
|--------------------------------------------------------------------------
|
| Before intalled the application key won't be set and there will
| be an error when running the install route, in this case, we will
| include the PreInstal file to configure at least the APP_KEY environment variable
| so the installation can be performed properly
|
*/
$rootDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR;

 if (! file_exists($rootDir . 'storage' . DIRECTORY_SEPARATOR . \App\Innoclapps\Application::INSTALLED_FILE)) {
     ini_set('display_errors', 'Off');

     (new \App\PreInstall($rootDir, \App\Installer\EnvironmentManager::guessUrl()))->init();

     if (strpos($_SERVER['REQUEST_URI'], \App\Innoclapps\Application::INSTALL_ROUTE_PREFIX) === false) {
         die(Header('Location: /' . \App\Innoclapps\Application::INSTALL_ROUTE_PREFIX));
     }
 }

unset($rootDir);

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
