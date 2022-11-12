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

use Illuminate\Database\Migrations\Migrator;

class Migration
{
    /**
     * Initialize new Migration instance
     *
     * @param \Illuminate\Database\Migrations\Migrator $migratior
     */
    public function __construct(protected Migrator $migrator)
    {
    }

    /**
     * Check whether the application requires migrations to be run
     *
     * @return boolean
     */
    public function needed() : bool
    {
        $ran = $this->migrator->getRepository()->getRan();
        $all = $this->getAllMigrationFiles();

        if (count($all) > 0) {
            return count($all) > count($ran);
        }

        return false;
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles() : array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get all of the migration paths.
     *
     * @return array
     */
    protected function getMigrationPaths() : array
    {
        return array_merge(
            $this->migrator->paths(),
            [$this->getMigrationPath()]
        );
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath() : string
    {
        return database_path() . DIRECTORY_SEPARATOR . 'migrations';
    }
}
