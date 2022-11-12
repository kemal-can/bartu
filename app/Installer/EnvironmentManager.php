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

use Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector;

class EnvironmentManager
{
    /**
     * @var string
     */
    protected $envPath;

    /**
     * Initialize new EnvironmentManager instance.
     *
     * @param string|null $envPath
     */
    public function __construct(?string $envPath = null)
    {
        $this->envPath = $envPath ?? base_path('.env');
    }

    /**
     * Save the form content to the .env file.
     *
     * @param \App\Installer\Environment $env
     *
     * @return bool
     */
    public function saveEnvFile(Environment $env)
    {
        $contents = 'APP_NAME=\'' . $env->getName() . "'\n" .
        'APP_KEY=' . $env->getKey() . "\n" .
        'IDENTIFICATION_KEY=' . $env->getIdentificationKey() . "\n" .
        'APP_URL=' . $env->getUrl() . "\n" .
        '#APP_DEBUG=' . 'true' . "\n\n" .
        'DB_CONNECTION=' . 'mysql' . "\n" .
        'DB_HOST=' . $env->getDbHost() . "\n" .
        'DB_PORT=' . $env->getDbPort() . "\n" .
        'DB_DATABASE=' . $env->getDbName() . "\n" .
        'DB_USERNAME=' . $env->getDbUser() . "\n" .
        'DB_PASSWORD=\'' . $env->getDbPassword() . "'\n\n" .
        'MAIL_MAILER=' . 'array' . "\n";

        $additional = $env->getAdditional();

        if (count($additional) > 0) {
            $contents .= "\n";

            foreach ($additional as $key => $value) {
                $contents .= $key . '=' . $value . "\n";
            }
        }

        try {
            file_put_contents($this->envPath, $contents);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the memory limit in MB
     *
     * @return int
     */
    public static function getMemoryLimitInMegabytes()
    {
        $limit = (new MemoryDataCollector)->getMemoryLimit();

        if ($limit === -1) {
            return $limit;
        }

        return $limit / (1024 * 1024);
    }

    /**
     * Guess the application URL
     *
     * @return string
     */
    public static function guessUrl()
    {
        $guessedUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $guessedUrl .= '://' . $_SERVER['HTTP_HOST'];
        $guessedUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $guessedUrl = preg_replace('/install.*/', '', $guessedUrl);

        return rtrim($guessedUrl, '/');
    }
}
