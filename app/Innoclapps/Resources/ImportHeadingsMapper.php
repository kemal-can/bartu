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

namespace App\Innoclapps\Resources;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithLimit;
use App\Innoclapps\Fields\FieldsCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Innoclapps\Contracts\Fields\Dateable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportHeadingsMapper implements ToCollection, WithHeadingRow, WithLimit
{
    /**
     * @var \Illuminate\Support\Colection
     */
    protected $collection;

    /**
     * The headings that are already mapped
     *
     * @var array
     */
    protected $mappedFields = [];

    /**
     * Map the given file and fields
     *
     * @param string $filePath
     * @param \App\Innoclapps\Fields\FieldsCollection $fields
     * @param string $disk
     *
     * @return static
     */
    public function __construct(string $filePath, protected FieldsCollection $fields, string $disk)
    {
        Excel::import($this, $filePath, $disk, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Map the actual heading with the fields
     *
     * @return \Illuminate\Support\Colection
     */
    public function map()
    {
        // We will get the first row key (the headings) to have something
        // to work with and from the headings will detect the appropriate fields
        return $this->collection->first()->keys()
            ->reject(fn ($heading) => is_numeric($heading))
            ->values()
            ->map(
                fn ($originalHeadingKey, $index) => $this->mapHeading($originalHeadingKey, $index)
            )->all();
    }

    /**
     * Map heading
     *
     * @param string $originalHeadingKey
     * @param int $index
     *
     * @return array
     */
    protected function mapHeading($originalHeadingKey, string $index) : array
    {
        return [
                'original'           => $originalHeadingKey,
                'detected_attribute' => $attribute = $this->detectFieldFromHeading(
                    $originalHeadingKey,
                    $index
                )?->attribute,
                // User-selected attribute, default the detected one
                'attribute'     => $attribute,
                'preview'       => $this->previewRecords($originalHeadingKey)->implode(', '),
                'skip'          => ! (bool) $attribute,
                'auto_detected' => (bool) $attribute,
            ];
    }

    /**
     * Get records for preview
     *
     * @param string $heading
     *
     * @return \Illuminate\Support\Collection
     */
    public function previewRecords($heading)
    {
        return $this->collection->filter(fn ($row) => ! empty($row[$heading]))
            ->take(3)
            ->map(fn ($row) => $row[$heading]);
    }

    /**
     * Find field from the given heading
     *
     * @param string $heading
     * @param int $index
     *
     * @return \App\Innoclapps\Fields\Field|null
     */
    protected function detectFieldFromHeading($heading, $index)
    {
        return tap($this->fields->first(function ($field, $fIndex) use ($heading, $index) {
            return ! array_key_exists($field->attribute, $this->mappedFields) &&
                        (
                            Str::lower($field->label) === Str::lower($heading) ||
                            $field->attribute === $heading ||
                            // Same index order, slightly changes in heading
                            $fIndex === $index && Str::contains($heading, $field->label) ||
                            // E.q. recognize "Field Label (UTC)" heading to "Field Label" or "field_attribute"
                            $field instanceof Dateable && Str::endsWith($heading, '(UTC)') && Str::contains($heading, [$field->label, $field->attribute])
                        );
        }), function ($field) {
            // We will store all fields that are already mapped to a heading to prevent
            // mapping the same field to another heading that contains the field label
            // e.q. if we have 2 fields "field name" and "another field name"
            // The "another field name" will be mapped with the "field name" heading/field
            // as it contains the actual name in heading
            if ($field) {
                $this->mappedFields[$field->attribute] = true;
            }
        });
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return int
     */
    public function limit() : int
    {
        return 100;
    }
}
