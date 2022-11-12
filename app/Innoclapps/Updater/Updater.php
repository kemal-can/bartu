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

namespace App\Innoclapps\Updater;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use App\Innoclapps\Updater\Events\UpdateSucceeded;
use App\Innoclapps\Updater\Exceptions\UpdaterException;
use App\Innoclapps\Updater\Exceptions\PurchaseKeyUsedException;
use App\Innoclapps\Updater\Exceptions\PurchaseKeyEmptyException;
use App\Innoclapps\Updater\Exceptions\InvalidPurchaseKeyException;
use App\Innoclapps\Updater\Exceptions\HasWrongPermissionsException;
use App\Innoclapps\Updater\Exceptions\ReleaseDoesNotExistsException;
use App\Innoclapps\Updater\Exceptions\MinPHPVersionRequirementException;

class Updater
{
    use ChecksPermissions, ExchangesPurchaseKey;

    /**
     * @var int
     */
    const MIN_PHP_VERSION_REQUIREMENT_CODE = 495;

    /**
     * @var int
     */
    const RELEASE_DOES_NOT_EXISTS_CODE = 496;

    /**
     * @var int
     */
    const PURCHASE_KEY_USED_CODE = 499;

    /**
     * @var int
     */
    const INVALID_PURCHASE_KEY_CODE = 498;

    /**
     * @var int
     */
    const PURCHASE_KEY_EMPTY_CODE = 497;

    /**
     * Available releases cache
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $releases;

    /**
     * The path where the update files will be extracted
     *
     * @var string
     */
    protected string $basePath;

    /**
     * Initialize new Updater instance.
     *
     * @param \GuzzleHttp\Client $client
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param array $config
     */
    public function __construct(protected Client $client, protected Filesystem $filesystem, protected array $config)
    {
        $this->basePath = base_path();
        $this->releases = new Collection;
        $this->filesystem->ensureDirectoryExists($this->getDownloadPath());
    }

    /**
     * Fetch the latest version, optionally provide a version to fetch.
     *
     * @param string $version
     *
     * @return \App\Innoclapps\Updater\Release
     *
     * @throws \App\Innoclapps\Updater\Exceptions\UpdaterException
     */
    public function fetch(string $version = '') : Release
    {
        $release = $this->find($version);

        if (! $release->archive()->exists()) {
            $this->download($release);
        }

        return $release;
    }

    /**
     * Find a release by given version
     *
     * @param string $version
     *
     * @return \App\Innoclapps\Updater\Release
     *
     * @throws \App\Innoclapps\Updater\Exceptions\UpdaterException
     */
    public function find(string $version) : Release
    {
        $releases = $this->getAvailableReleases();

        if ($releases->isEmpty()) {
            throw new UpdaterException("The {$version} version could not be found.", 404);
        }

        // If version is not provided, will use the latest
        $release = $releases->first();

        if (! empty($version) &&
         $found = $releases->first(fn ($release) => $release->getVersion() === $version)) {
            /** @var \App\Innoclapps\Updater\Release */
            $release = $found;
        }

        return $release;
    }

    /**
     * Perform the given release update process.
     *
     * @param \App\Innoclapps\Updater\Release|string $release
     *
     * @return boolean
     *
     * @throws \App\Innoclapps\Updater\Exceptions\HasWrongPermissionsException
     */
    public function update(Release|string $release) : bool
    {
        if (! $this->checkPermissions($this->basePath)) {
            throw new HasWrongPermissionsException;
        }

        if (is_string($release)) {
            $release = $this->fetch($release);
        }

        if (! $release->archive()->exists()) {
            $this->download($release);
        }

        $release->archive()
            ->excludedDirectories($this->config['exclude_folders'])
            ->excludedFiles($this->config['exclude_files'])
            ->extract($this->basePath);

        settings(['_last_updated_date' => date('Y-m-d H:i:s')]);

        event(new UpdateSucceeded($release));

        return true;
    }

    /**
     * Get the available releases
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \App\Innoclapps\Updater\Exceptions\UpdaterException
     */
    public function getAvailableReleases() : Collection
    {
        if ($this->releases->isNotEmpty()) {
            return $this->releases;
        }

        if (empty($url = $this->config['archive_url'])) {
            throw new UpdaterException('Archive URL not specified, please enter a valid URL in your config.', 500);
        }

        try {
            $count = preg_match_all(
                "/\d+\.\d+\.\d+.zip/i",
                $this->client->get(
                    static::createInternalRequestUrl($url)
                )->getBody()->getContents(),
                $files
            );
        } catch (RequestException $e) {
            throw new UpdaterException($e->getMessage(), $e->getCode());
        }

        $url = preg_replace('/\/$/', '', $url);

        for ($i = 0; $i < $count; $i++) {
            $version    = basename(preg_replace('/.zip$/', '', $files[0][$i]));
            $zipBallUrl = $url . '/' . 'v' . $files[0][$i];

            $release = (new Release($version, $this->filesystem))
                ->setStoragePath(
                    Str::finish($this->config['download_path'], DIRECTORY_SEPARATOR) . $version . '.zip'
                )
                ->setAccessToken($this->getPurchaseKey())
                ->setDownloadUrl(static::createInternalRequestUrl($zipBallUrl));

            $this->releases->push($release);
        }

        // Sort releases alphabetically descending to have newest package as first
        return $this->releases = $this->releases->sortByDesc(
            fn ($release) => $release->getVersion()
        )->values();
    }

    /**
     * Get the latest available version.
     * Example: 2.6.5
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getVersionAvailable() : string
    {
        $releaseCollection = $this->getAvailableReleases();

        if ($releaseCollection->isEmpty()) {
            return '';
        }

        return $releaseCollection->first()->getVersion();
    }

    /**
     * Get the version that is currenly installed.
     *
     * @return string
     */
    public function getVersionInstalled() : string
    {
        return $this->config['version_installed'];
    }

    /**
     * Check whether a new version is available.
     *
     * @param string $currentVersion
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function isNewVersionAvailable(string $currentVersion = '') : bool
    {
        $version = $currentVersion ?: $this->getVersionInstalled();

        if (! $version) {
            throw new InvalidArgumentException('No currently installed version specified.');
        }

        return version_compare($version, $this->getVersionAvailable(), '<');
    }

    /**
     * Get the updater download path
     *
     * @param string $glue
     *
     * @return string
     */
    public function getDownloadPath(string $glue = '') : string
    {
        return $this->config['download_path'] . $glue;
    }

    /**
     * Create an internal request URL with necessary information
     *
     * @param string $endpoint
     * @param array $extra
     *
     * @return string
     */
    public static function createInternalRequestUrl(string $endpoint, array $extra = []) : string
    {
        return $endpoint . '?' . http_build_query(array_merge([
            'identification_key'      => config('innoclapps.key'),
            'app_url'                 => config('app.url'),
            'installed_version'       => \App\Innoclapps\Application::VERSION,
            'server_ip'               => settings('_server_ip'),
            'installed_date'          => settings('_installed_date'),
            'last_updated_date'       => settings('_last_updated_date'),
            'php_version'             => PHP_VERSION,
            'locale'                  => app()->getLocale(),
            'database_driver_version' => settings('_db_driver_version'),
            'database_driver'         => settings('_db_driver'),
      ], $extra));
    }

    /**
     * Download the release .zip file
     *
     * @param \App\Innoclapps\Updater\Release $release
     *
     * @return void
     */
    protected function download(Release $release) : void
    {
        try {
            $release->download($this->client);
        } catch (ClientException $e) {
            if ($e->getCode() === static::MIN_PHP_VERSION_REQUIREMENT_CODE) {
                throw new MinPHPVersionRequirementException;
            } elseif ($e->getCode() === static::RELEASE_DOES_NOT_EXISTS_CODE) {
                throw new ReleaseDoesNotExistsException;
            } elseif ($e->getCode() === static::PURCHASE_KEY_USED_CODE) {
                throw new PurchaseKeyUsedException;
            } elseif ($e->getCode() === static::INVALID_PURCHASE_KEY_CODE) {
                throw new InvalidPurchaseKeyException;
            } elseif ($e->getCode() === static::PURCHASE_KEY_EMPTY_CODE) {
                throw new PurchaseKeyEmptyException;
            }

            throw $e;
        }
    }
}
