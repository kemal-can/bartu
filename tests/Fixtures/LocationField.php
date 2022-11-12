<?php

namespace Tests\Fixtures;

use App\Innoclapps\Fields\MorphMany;
use App\Innoclapps\Table\MorphManyColumn;

class LocationField extends MorphMany
{
    /**
     * Display key
     *
     * @var string
     */
    public $displayKey = 'display_name';

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\HasManyColumn
     */
    public function indexColumn() : MorphManyColumn
    {
        return tap(new MorphManyColumn(
            $this->morphManyRelationship,
            $this->displayKey,
            $this->label
        ), function ($column) {
            $column->select('display_type');
        });
    }

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->setJsonResource(LocationJsonResource::class);

        $this->prepareForValidation(function ($value, $request, $validator, $data) {
            if (is_array($value)) {
                $this->handleValidation($value, $validator, $request);
            }

            return $value;
        })->provideImportValueSampleUsing(function () {
            return 'Port Avenue ';
        });
    }

    /**
     * Handle the location field validation
     *
     * @param array &$value
     * @param Validator $validator
     *
     * @return Void
     */
    protected function handleValidation(&$value, $validator, $request)
    {
        foreach ($value as $key => $location) {
            $value[$key]['_track_by'] = ['display_name' => $location['display_name']];

            if (empty($location['display_name']) && isset($location['id'])) {
                $value[$key]['_delete'] = true;
            } elseif (empty($location['display_name'])) {
                if ($this->isRequired($request)) {
                    $validator->after(function ($validator) use ($key) {
                        $validator->errors()->add(
                            $this->requestAttribute() . '.' . $key . '.display_name',
                            __('validation.required')
                        );
                    });
                } else {
                    $value[$key]['_skip'] = true;
                }
            }
        }
    }
}
