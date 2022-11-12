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

namespace App\Innoclapps\Settings\Stores;

use JsonException;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Filesystem\Filesystem;
use App\Innoclapps\Settings\Utilities\Arr;

/**
 * @codeCoverageIgnore
 * NOT USED YET
 */
class JsonStore extends AbstractStore
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string
     */
    protected string $backupPath;

    /**
     * Fire the post options to customize the store.
     *
     * @param array $options
     */
    protected function postOptions(array $options)
    {
        $this->path       = Arr::get($options, 'path');
        $this->backupPath = $this->path . '.backup';
    }

    /**
     * Read the data from the store.
     *
     * @return array
     */
    protected function read() : array
    {
        if (! $this->filesystem()->exists($this->path) && ! $this->hasBackup()) {
            return [];
        }

        $path     = $this->hasBackup() ? $this->backupPath : $this->path;
        $contents = $this->filesystem()->get($path, true);

        try {
            $data = json_decode($contents, true);
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid JSON file in [{$this->path}]");
        }

        return (array) $data;
    }

    /**
     * Write the data into the store.
     *
     * @param array $data
     */
    protected function write(array $data) : void
    {
        $contents = $data ? json_encode($data, JSON_PRETTY_PRINT) : '{}';

        try {
            $this->filesystem()->put($this->path, $contents, true);

            if ($this->hasBackup()) {
                $this->filesystem()->delete($this->backupPath);
            }
        } catch (\ErrorException $e) {
            // file_put_contents(): Write of 1720 bytes failed with errno=71 Protocol error
            Log::error('Failed to save settings: ' . $e->getMessage());
            $this->filesystem()->put($this->backupPath, $contents);
        }
    }

    /**
     * Checkw whether the settings are in backup because of failure to save
     *
     * @return boolean
     */
    protected function hasBackup() : bool
    {
        return $this->filesystem()->exists($this->backupPath);
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    protected function filesystem() : Filesystem
    {
        return $this->app['files'];
    }
}
