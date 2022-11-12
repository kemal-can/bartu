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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Innoclapps\Updater\Updater;
use Illuminate\Filesystem\Filesystem;
use App\Installer\RequirementsChecker;
use App\Innoclapps\Translation\Translation;

class UpdateCommand extends Command
{
    /**
     * Create a new config cache command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     *
     * @return void
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bartu:update {--key= : Purchase key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the application to the latest available version.';

    /**
     *  Execute the console command.
     *
     * @param \App\Installer\RequirementsChecker $requirements
     * @param \App\Innoclapps\Updater\Updater $updater
     *
     * @return void
     */
    public function handle(RequirementsChecker $requirements, Updater $updater)
    {
        abort_unless(
            $requirements->passes('zip'),
            409,
            __('update.update_zip_is_required')
        );

        $this->info('Configuring purchase key');
        $updater->usePurchaseKey($this->option('key') ?: '');

        if (! $updater->isNewVersionAvailable()) {
            $this->info('The latest version ' . $updater->getVersionInstalled() . ' is already installed.');

            return;
        }

        $this->warn('Updating... this may take several minutes');

        $this->info('Putting the application into maintenance mode');
        $this->call('down', ['--render' => 'errors.updating']);

        try {
            if (! $this->getLaravel()->runningUnitTests()) {
                $this->info('Increasing PHP config values');

                $this->increasePhpIniValues();
            }

            $this->info('Performing update');
            $updater->update($latestVersion = $updater->getVersionAvailable());

            $this->info('Clearing cache and temporary files');
            $this->call('bartu:clear-cache');
            $this->call('bartu:updater-clear');

            $this->info('Generating JSON language file');
            Translation::generateJsonLanguageFile();

            if (! $this->laravel->runningUnitTests() && $this->laravel->environment('production')) {
                $this->info('Optimizing application');
                $this->call('bartu:optimize');

                // After the application is optimized and the config is cached, we will manually update
                // the version in the cached config file with the release version because the updater config is using
                // \App\Innoclapps\Application::VERSION to set the installed version but because it's the same process
                // when the update is performed when accessing \App\Innoclapps\Application::VERSION will return the old
                // version and the config will be cached with the old version instead of the new one
                $this->updateCachedConfigInstalledVersion($latestVersion);
            }

            $this->info('Running migrations');
            $this->call('migrate', ['--force' => true]);

            // Restart the queue (if configured)
            try {
                $this->call('queue:restart');
            } catch (\Exception $e) {
                $this->warn($e->getMessage());
            }
        } finally {
            $this->info('Bringing the application out of maintenance mode');
            $this->call('up');
        }
    }

    /**
     * Update the cached config installed version
     *
     * @param string $version
     *
     * @return void
     */
    protected function updateCachedConfigInstalledVersion(string $version)
    {
        $configPath = $this->laravel->getCachedConfigPath();
        $config     = require($configPath);

        $config['updater']['version_installed'] = $version;

        $this->files->put(
            $configPath,
            '<?php return ' . var_export($config, true) . ';' . PHP_EOL
        );
    }

    /**
     * Incrase PHP ini values before performing an update
     *
     * @return void
     */
    protected function increasePhpIniValues() : void
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', 360);
    }
}
