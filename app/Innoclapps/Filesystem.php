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

namespace App\Innoclapps;

use ReflectionClass;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    /**
     * List files in a given directory which as subclass of a given class name
     *
     * @param string $className
     * @param string $directory
     *
     * @return array
     */
    public static function listClassFilesOfSubclass($className, $directory)
    {
        $namespace = app()->getNamespace();

        $classes = [];

        foreach ((new Finder)->in($directory)->files() as $class) {
            $class = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($class->getPathname(), app_path() . DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($class, $className) &&
                ! (new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
