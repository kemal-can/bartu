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

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use App\Innoclapps\Contracts\Fields\Dateable;

abstract class SampleViaFields implements FromArray
{
    use ProvidesImportableFields;

    /**
     * Provides the importable fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    abstract public function fields();

    /**
     * Resolve the fields for the sample data
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function resolveSampleFields()
    {
        return $this->resolveFields()->reject(fn ($field) => $field->excludeFromImportSample);
    }

    /**
     * Download sample
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        return Excel::download($this, 'sample.csv');
    }

    /**
     * Creates the sample data rows
     *
     * The function is used in the interface from excel FromArray which is
     * implemented on the main sample classes
     *
     * @return array
     */
    public function array() : array
    {
        return [
            $this->getHeadings(),
            $this->getRow(),
        ];
    }

    /**
     * Get sample headings by fields
     *
     * @return array
     */
    public function getHeadings() : array
    {
        return $this->resolveSampleFields()->map(function ($field) {
            if ($field instanceof Dateable) {
                return $field->label . ' (' . config('app.timezone') . ')';
            }

            return $field->label;
        })->all();
    }

    /**
     * Prepares import sample row
     *
     * @return array
     */
    public function getRow() : array
    {
        return $this->resolveSampleFields()->reduce(function ($carry, $field) {
            $carry[] = $field->sampleValueForImport();

            return $carry;
        }, []);
    }
}
