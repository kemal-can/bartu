<?php

namespace Tests\Concerns;

use App\Innoclapps\Facades\Fields;

trait TestsCustomFields
{
    protected function fieldsTypesThatRequiresDatabaseColumnCreation()
    {
        return collect(Fields::customFieldsAble())->map(function ($field) {
            return new \ReflectionClass($field);
        })->map(function ($class) {
            return $class->newInstanceWithoutConstructor();
        })->reject->isMultiOptionable()->map(function ($class) {
            return class_basename($class);
        })->values()->all();
    }

    protected function fieldsTypesThatDoesntRequiresDatabaseColumnCreation()
    {
        return collect(Fields::customFieldsAble())->map(function ($field) {
            return new \ReflectionClass($field);
        })->map(function ($class) {
            return $class->newInstanceWithoutConstructor();
        })->filter->isMultiOptionable()->map(function ($class) {
            return class_basename($class);
        })->values()->all();
    }
}
