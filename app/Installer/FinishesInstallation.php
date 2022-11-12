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

namespace App\Installer;

use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\Facades\Artisan;
use App\Innoclapps\Facades\ChangeLogger;
use App\Innoclapps\Translation\Translation;

trait FinishesInstallation
{
    /**
     * Finalize the installation
     *
     * @return array
     */
    protected function finalizeInstallation($currency, $country)
    {
        $errors = ['general' => ''];

        ChangeLogger::disable();
        $this->generateLanguageFile();

        try {
            Artisan::call('storage:link');
        } catch (\Exception $e) {
            $errors['general'] .= "Failed to create storage symlink.\n";
        }

        $this->seed();

        // Save default settings from installation
        settings([
            'currency'           => $currency,
            'company_country_id' => $country,
        ]);

        if (! Innoclapps::createInstalledFile()) {
            $errors['general'] .= 'Failed to create the installed file. (' . Innoclapps::installedFileLocation() . ").\n";
        }

        $this->captureEnvVariables([
            '_installed_date' => date('Y-m-d H:i:s'),
        ]);

        $this->optimize();

        return empty($errors['general']) ? [] : $errors;
    }

    /**
     * Migrate the database
     *
     * @return void
     */
    protected function migrate()
    {
        Artisan::call('migrate', [
            '--force' => true,
        ]);
    }

    /**
     * Seed the application database
     *
     * @return void
     */
    protected function seed()
    {
        Artisan::call('db:seed', [
            '--class' => \SystemSeeder::class,
            '--force' => true,
        ]);
    }

    /**
     * Generate language file
     *
     * @return void
     */
    protected function generateLanguageFile()
    {
        Translation::generateJsonLanguageFile();
    }

    /**
     * Cache the application bootstrap files
     *
     * @return void
     */
    protected function optimize()
    {
        Artisan::call('bartu:optimize');
    }

    /**
     * Create a capture of the env variables in database
     * NOTE: Used in RequirementsController as well for URL confirmation
     *
     * @param array $extra
     *
     * @return void
     */
    protected function captureEnvVariables($extra = [])
    {
        settings(array_merge([
            '_app_url'           => config('app.url'),
            '_server_ip'         => $_SERVER['SERVER_ADDR'] ?? '',
            '_db_driver_version' => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            '_db_driver'         => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            '_php_version'       => PHP_VERSION,
        ], $extra));
    }
}
