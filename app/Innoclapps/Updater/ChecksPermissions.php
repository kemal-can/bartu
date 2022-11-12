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

use Symfony\Component\Finder\Finder;

trait ChecksPermissions
{
    /**
     * @var callable|null
     */
    protected static $permissionsCheckerFinderUsing;

    /**
     * Check a given directory recursively if all files are writeable.
     *
     * @param string $path
     *
     * @return boolean
     */
    protected function checkPermissions(string $path) : bool
    {
        $passes = true;
        $finder = $this->getPermissionsCheckerFinderInstance($path);

        foreach ($finder as $file) {
            if ($file->isWritable() === false) {
                $passes = false;

                break;
            }
        }

        return $passes;
    }

    /**
     * Get the finder instance for the permissions checker
     *
     * @param string $path
     *
     * @return \Symfony\Component\Finder\Finder
     */
    protected function getPermissionsCheckerFinderInstance(string $path) : Finder
    {
        if (static::$permissionsCheckerFinderUsing) {
            return call_user_func_array(static::$permissionsCheckerFinderUsing, [$path]);
        }

        return (new Finder())->exclude([
            'node_modules',
            'tests/coverage',
            'bartu_crm',
            'vendor/bartucrm/hosted',
        ])->notName('worker.log')->in($path);
    }

    /**
     * Provide custom permissions checker Finder instance
     *
     * @param callable|null $callback
     *
     * @return void
     */
    public static function providePermissionsCheckerFinderUsing(?callable $callback) : void
    {
        static::$permissionsCheckerFinderUsing = $callback;
    }
}
