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

use Exception;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Innoclapps\Facades\Innoclapps;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Innoclapps\Contracts\Fields\Dateable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Innoclapps\Resources\Http\ImportRequest;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Innoclapps\Import\Exceptions\ImportException;
use App\Innoclapps\Import\Exceptions\ValidationException;

abstract class ImportViaFields implements ToArray, WithHeadingRow, WithMapping, SkipsEmptyRows, WithChunkReading
{
    use ProvidesImportableFields,
        ValidatesFields;

    /**
     * Provides the available fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    abstract public function fields();

    /**
     * Perform import
     *
     * @throws \App\Innoclapps\Import\Exceptions\ValidationException
     * @throws \App\Innoclapps\Import\Exceptions\ImportException
     *
     * @param string|\Illuminate\Http\UploadedFile $filePath
     * @param string $disk
     *
     * @return void
     */
    public function perform(UploadedFile|string $filePath, string $disk = 'local')
    {
        Innoclapps::setImportStatus('in-progress');

        try {
            Excel::import($this, $filePath, $disk, \Maatwebsite\Excel\Excel::CSV);
        } catch (ValidationException $e) {
            throw new ValidationException($e->getMessage(), $e->errors());
        } catch (ImportException|Exception $e) {
            throw new ImportException($e->getMessage());
        } finally {
            Innoclapps::setImportStatus(false);
        }
    }

    /**
     * Finalize the given request data
     *
     * @param \App\Innoclapps\Resources\Http\ImportRequest $request
     *
     * @return \App\Innoclapps\Resources\Http\ImportRequest
     */
    protected function finalizeRequestData(ImportRequest $request) : ImportRequest
    {
        foreach ($this->resolveFields() as $field) {
            // We will check if the actual field is found in the csv row
            // if not, we will just remove from the request to prevent any issues
            if ($request->missing($field->attribute)) {
                $request->replace($request->except($field->attribute));

                continue;
            }
            $value = $request->input($field->attribute);

            if ($attributes = $field->resolveForImport(
                is_string($value) ? trim($value) : $value,
                $request->all(),
                $request->original()
            )) {
                $request->merge($attributes);
            }
        }

        return $request->replace(
            collect($request->all())->filter()->all()
        );
    }

    /**
     * @param array $row
     *
     * @return array
     */
    public function map($row) : array
    {
        return $this->resolveFields()->reduce(function ($carry, $field) use ($row) {
            $label = $field->label;

            // As the date fields have the timezine in the headings column
            // we must use the same format here to properly find the row
            if ($field instanceof Dateable) {
                $label .= ' (' . config('app.timezone') . ')';
            }

            $carry[$field->attribute] = $row[$label] ?? null;

            return $carry;
        }, []);
    }

    public function chunkSize() : int
    {
        return 1000;
    }
}
