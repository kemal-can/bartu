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
use ReflectionMethod;
use Illuminate\Support\Facades\Gate;

trait ProvidesModelAuthorizations
{
    /**
     * Get all defined authorizations for the model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $without Exclude abilities from authorization
     *
     * @return array|null
     */
    public function getAuthorizations($model, $without = [])
    {
        if ($policy = policy($model)) {
            return collect((new ReflectionClass($policy))->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn ($method)      => in_array($method->name, $without))
                ->mapWithKeys(fn ($method) => [$method->name => Gate::allows($method->name, $model)])->all();
        }
    }
}
