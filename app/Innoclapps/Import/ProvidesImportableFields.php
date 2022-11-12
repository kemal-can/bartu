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

namespace App\Innoclapps\Import;

trait ProvidesImportableFields
{
    /**
     * Resolve the importable fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function resolveFields()
    {
        return $this->filterFieldsForImport(
            $this->fields()
        );
    }

    /**
     * Filter fields for import
     *
     * @param \App\Innoclapps\Fields\FieldsCollection $fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    protected function filterFieldsForImport($fields)
    {
        return $fields->reject(function ($field) {
            return $field->excludeFromImport || $field->isReadOnly();
        })->values();
    }
}
