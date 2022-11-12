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

namespace App;

use Illuminate\Support\Arr;

class PreInstall
{
    /**
     * @var string
     */
    public string $envPath;

    /**
     * @var string
     */
    public string $envExamplePath;

    /**
     * Initialize new PreInstall instance.
     *
     * @param string $rootDir
     */
    public function __construct(protected string $rootDir, protected string $url)
    {
        $this->url            = $url;
        $this->rootDir        = rtrim($rootDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->envPath        = $this->rootDir . '.env';
        $this->envExamplePath = $this->rootDir . '.env.example';
    }

    /**
     * Check whether the .env file exist
     *
     * @return boolean
     */
    private function envFileExist()
    {
        return file_exists($this->envPath);
    }

    /**
     * Check whether the .env.example file exist
     *
     * @return boolean
     */
    private function envExampleFileExist()
    {
        return file_exists($this->envExamplePath);
    }

    /**
     * Generate random application key
     *
     * @return string
     */
    private function generateAppRandomKey()
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Write the key in the environment file
     *
     * @param string $key
     *
     * @return void
     */
    private function writeKeyInEnvironmentFile($key)
    {
        file_put_contents($this->envPath, preg_replace(
            '/^APP_KEY=.*/m',
            'APP_KEY=' . $key,
            file_get_contents($this->envPath)
        ));
    }

    /**
     * Write the APP_URL value in the environment file
     *
     * @param string $url
     *
     * @return void
     */
    private function writeUrlInEnvironmentFile($url)
    {
        file_put_contents($this->envPath, preg_replace(
            '/^APP_URL=.*/m',
            'APP_URL=' . $url,
            file_get_contents($this->envPath)
        ));
    }

    /**
     * Write the IDENTIFICATION_KEY value in the environment file
     *
     * @param string $key
     *
     * @return void
     */
    private function writeIdentificationKeyInEnvironmentFile($key)
    {
        file_put_contents($this->envPath, preg_replace(
            '/^IDENTIFICATION_KEY=.*/m',
            'IDENTIFICATION_KEY=' . $key,
            file_get_contents($this->envPath)
        ));
    }

    /**
     * Get cached config value
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getCachedConfigValue($key)
    {
        if (file_exists($this->rootDir . 'bootstrap/cache/config.php')) {
            $config = include $this->rootDir . 'bootstrap/cache/config.php';

            if (! empty($config)) {
                return ! empty(Arr::get($config, $key)) ? Arr::get($config, $key) : '';
            }
        }
    }

    /**
     * Get config value from .env
     *
     * @param string $envKey
     *
     * @return string
     */
    private function getEnvConfigValue($envKey)
    {
        // Read .env file into $_ENV
        try {
            \Dotenv\Dotenv::create(
                \Illuminate\Support\Env::getRepository(),
                $this->rootDir
            )->load();
        } catch (\Exception $e) {
            // Do nothing
        }

        return ! empty($_ENV[$envKey]) ? $_ENV[$envKey] : '';
    }

    /**
     * Clear the application config cache
     *
     * @return void
     */
    private function clearConfigCache()
    {
        if (file_exists($this->rootDir . 'bootstrap/cache/config.php')) {
            unlink($this->rootDir . 'bootstrap/cache/config.php');
        }
    }

    /**
     * Show installer error
     *
     * @param string $msg
     *
     * @return void
     */
    private function showPreInstallError($msg) : never
    {
        echo $msg;
        die;
    }

    /**
     * Create the initial .env file if not exist
     *
     * @return void
     */
    protected function createEnvFileIfNotExists()
    {
        // Check if .env.example exists
        if ($this->envFileExist()) {
            return;
        }

        if (! $this->envExampleFileExist()) {
            $this->showPreInstallError(
                'File <strong>.env.example</strong> not found. Please make sure to copy this file from the downloaded files.'
            );
        }

        // Copy .env.example
        copy($this->envExamplePath, $this->envPath);

        if (! $this->envFileExist()) {
            $this->showPermissionsError();
        }
    }

    /**
     * Init the pre-installation
     *
     * @return void
     */
    public function init()
    {
        $this->createEnvFileIfNotExists();

        $envAppKey    = $this->getEnvConfigValue('APP_KEY');
        $cachedAppKey = $this->getCachedConfigValue('app.key');
        $this->makeSureEmptyKeyIsPresent('APP_KEY');

        if ($cachedAppKey && ! $envAppKey) {
            $this->writeKeyInEnvironmentFile($cachedAppKey);
        } elseif ($envAppKey && ! $cachedAppKey) {
            $this->writeKeyInEnvironmentFile($envAppKey);
        } elseif (! $envAppKey) {
            $this->writeKeyInEnvironmentFile($this->generateAppRandomKey());
        }

        $envIdentificationKey    = $this->getEnvConfigValue('IDENTIFICATION_KEY');
        $cachedIdentificationKey = $this->getCachedConfigValue('innoclapps.key');
        $this->makeSureEmptyKeyIsPresent('IDENTIFICATION_KEY');

        if ($cachedIdentificationKey && ! $envIdentificationKey) {
            $this->writeIdentificationKeyInEnvironmentFile($cachedIdentificationKey);
        } elseif ($envIdentificationKey && ! $cachedIdentificationKey) {
            $this->writeIdentificationKeyInEnvironmentFile($envIdentificationKey);
        } elseif (! $envIdentificationKey) {
            $this->writeIdentificationKeyInEnvironmentFile((string) \Illuminate\Support\Str::uuid());
        }

        $this->makeSureEmptyKeyIsPresent('APP_URL');
        $this->writeUrlInEnvironmentFile($this->url);

        $this->clearConfigCache();
    }

    /**
     * Make sure that the needed keys are present in the .env file
     *
     * @return void
     */
    private function makeSureEmptyKeyIsPresent($key)
    {
        // Add APP_KEY= to the .env file if needed
        // Without APP_KEY= the key will not be saved
        if (! preg_match('/^' . $key . '=/m', file_get_contents($this->envPath))) {
            if (! file_put_contents($this->envPath, PHP_EOL . $key . '=', FILE_APPEND)) {
                $this->showPermissionsError();
            }
        }
    }

    /**
     * Helper function to show permissions error
     *
     * @return void
     */
    private function showPermissionsError()
    {
        $rootDirNoSlash = rtrim($this->rootDir, DIRECTORY_SEPARATOR);

        $this->showPreInstallError('<div style="font-size:18px;">Web installer could not write data into <strong>' . $this->envPath . '</strong> file. Please give your web server user (<strong>' . get_current_process_user() . '</strong>) write permissions in <code><pre style="background: #f0f0f0;
            padding: 15px;
            width: 50%;
            margin-top:0px;
            border-radius: 4px;">
sudo chown ' . get_current_process_user() . ':' . get_current_process_user() . ' -R ' . $rootDirNoSlash . '
sudo find ' . $rootDirNoSlash . ' -type d -exec chmod 755 {} \;
sudo find ' . $rootDirNoSlash . ' -type f -exec chmod 644 {} \;
</pre></code>');
    }
}
