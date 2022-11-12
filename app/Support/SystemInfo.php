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

namespace App\Support;

use JsonSerializable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Contracts\Support\Arrayable;

class SystemInfo implements JsonSerializable, Arrayable, FromArray
{
    /**
     * Initialize new SystemInfo class
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'OS'                  => PHP_OS,
            'Webserver'           => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
            'Server Protocol'     => isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'N/A',
            'PHP Version'         => PHP_VERSION,
            'PHP IMAP Extension'  => extension_loaded('imap'),
            'PHP ZIP Extension'   => extension_loaded('zip'),
            'max_input_vars'      => ini_get('max_input_vars') ?: 'N/A',
            'upload_max_filesize' => ini_get('upload_max_filesize') ?: 'N/A',
            'post_max_size'       => ini_get('post_max_size') ?: 'N/A',
            'max_execution_time'  => ini_get('max_execution_time') ?: 'N/A',
            'memory_limit'        => ini_get('memory_limit') ?: 'N/A',
            'PHP Executable'      => \App\Innoclapps\Application::getPhpExecutablePath() ?: 'N/A',
            'Installed Version'   => \App\Innoclapps\Application::VERSION,
            'CloudFlare'          => $this->request->headers->has('Cf-Ray') ? 'Yes' : 'No',

            'Last Cron Run' => ! empty(settings('last_cron_run')) ?
                                        Carbon::parse(settings('last_cron_run'))->diffForHumans() :
                                        'N/A',

            'Installation Path' => base_path(),
            'Installation Date' => settings('_installed_date'),
            'Last Updated Date' => settings('_last_updated_date') ?: 'N/A',

            'Current Process User' => get_current_process_user(),
            'DB Driver Version'    => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'DB Driver'            => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            'DB_CONNECTION'        => config('database.default'),

            'APP_ENV'                            => config('app.env'),
            'APP_URL'                            => config('app.url'),
            'APP_DEBUG'                          => config('app.debug'),
            'SANCTUM_STATEFUL_DOMAINS'           => config('sanctum.stateful'),
            'MAIL_MAILER'                        => config('mail.default'),
            'CACHE_DRIVER'                       => config('cache.default'),
            'SESSION_DRIVER'                     => config('session.driver'),
            'SESSION_LIFETIME'                   => config('session.lifetime'),
            'QUEUE_CONNECTION'                   => config('queue.default'),
            'LOG_CHANNEL'                        => config('logging.default'),
            'SETTINGS_DRIVER'                    => config('settings.default'),
            'MEDIA_DISK'                         => config('mediable.default_disk'),
            'FILESYSTEM_DISK'                    => config('filesystems.default'),
            'FILESYSTEM_CLOUD'                   => config('filesystems.cloud'),
            'BROADCAST_DRIVER'                   => config('broadcasting.default'),
            'ENABLE_FAVICON'                     => config('app.favicon_enabled'),
            'HTML_PURIFY'                        => config('app.security.purify'),
            'MAIL_CLIENT_SYNC_INTERVAL'          => config('app.mail_client.sync.every'),
            'SYNC_INTERVAL'                      => config('app.sync.every'),
            'USER_INVITATION_EXPIRES_AFTER'      => config('app.invitation.expires_after'),
            'PREFERRED_DEFAULT_HOUR'             => config('app.defaults.hour'),
            'PREFERRED_DEFAULT_MINUTES'          => config('app.defaults.minutes'),
            'PREFERRED_DEFAULT_REMINDER_MINUTES' => config('app.defaults.reminder_minutes'),
            'PRUNE_TRASHED_RECORDS_AFTER'        => config('innoclapps.soft_deletes.prune_after'),
        ];
    }

    /**
     * Array function for the export
     *
     * @return array
     */
    public function array() : array
    {
        return [collect($this->toArray())->map(function ($value, $variableName) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            return [$variableName, $value];
        })];
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
}
