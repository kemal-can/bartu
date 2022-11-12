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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Artisan;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Facades\MailableTemplates;

class SystemToolsController extends ApiController
{
    /**
     * Generate i18n file
     *
     * @return void
     */
    public function i18n()
    {
        // i18n tool flag

        Translation::generateJsonLanguageFile();
    }

    /**
     * Clear application cache
     *
     * @return void
     */
    public function clearCache()
    {
        // Clear cache tool flag

        Artisan::call('bartu:clear-cache');

        // Restart the queue (if configured)
        try {
            Artisan::call('queue:restart');
        } catch (\Exception $e) {
        }
    }

    /**
     * Create application storage link
     *
     * @return void
     */
    public function storageLink()
    {
        // Storage link tool flag

        Artisan::call('storage:link');
    }

    /**
     * Run the database migrations
     *
     * @return void
     */
    public function migrate()
    {
        // Migrate tool flag

        ini_set('memory_limit', '256M');

        Artisan::call('migrate --force');
    }

    /**
     * Cache the framework bootstrap files
     *
     * @return void
     */
    public function optimize()
    {
        // Optimize tool flag

        Artisan::call('bartu:optimize');

        // Restart the queue (if configured)
        try {
            Artisan::call('queue:restart');
        } catch (\Exception $e) {
        }
    }

    /**
     * Seed the mailable templates
     *
     * @return void
     */
    public function seedMailableTemplates()
    {
        // Seed mailable templates tool flag

        MailableTemplates::seedIfRequired();
    }
}
