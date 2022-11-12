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

use Illuminate\Support\Arr;
use App\Innoclapps\Fields\MorphMany;
use App\Innoclapps\Contracts\Resources\ResourcefulRequestHandler;
use App\Innoclapps\Contracts\Fields\TracksMorphManyModelAttributes;
use App\Innoclapps\Contracts\Fields\HandlesChangedMorphManyAttributes;

class ResourcefulHandlerWithFields extends ResourcefulHandler implements ResourcefulRequestHandler
{
    /**
     * Handle the resource store action
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store()
    {
        [$attributes, $callbacks] = $this->getAttributes();

        $record = $this->handleAssociatedResources(
            $this->repository->create($attributes)
        );

        foreach ($this->morphManyFields() as $relation => $values) {
            foreach ($values ?? [] as $attributes) {
                $record->{$relation}()->create($attributes);
            }
        }

        $callbacks->each->__invoke($record);

        return $record;
    }

    /**
     * Handle the resource update action
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id)
    {
        [$attributes, $callbacks] = $this->getAttributes();

        $this->withSoftDeleteCriteria($this->repository);

        $record = $this->handleAssociatedResources(
            $this->repository->update($attributes, $id)
        );

        $this->syncMorphManyFields($record);

        $callbacks->each->__invoke($record);

        return $record;
    }

    /**
     * Get the morph many fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    protected function morphManyFields()
    {
        return $this->request->authorizedFields()
            ->whereInstanceOf(MorphMany::class)
            ->reject(fn ($field)      => $this->request->missing($field->requestAttribute()))
            ->mapWithKeys(fn ($field) => $field->storageAttributes($this->request, $field->requestAttribute()));
    }

    /**
     * Get the attributes for storage
     *
     * @return array
     */
    protected function getAttributes()
    {
        $parsed = $this->parseAttributes();

        $attributes = $parsed->reject(fn ($data) => is_callable($data['attributes']))
            ->mapWithKeys(
                fn ($data, $attribute) => $data['field'] ? $data['attributes'] : [$attribute => $data['value']]
            )->all();

        $callables = $parsed->filter(
            fn ($data)    => is_callable($data['attributes'])
        )->map(fn ($data) => $data['attributes']);

        return [$attributes, $callables];
    }

    /**
     * Get the attributes for the request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function parseAttributes()
    {
        return collect($this->request->all())->mapWithKeys(function ($value, $attribute) {
            $field = $this->request->authorizedFields()->findByRequestAttribute($attribute);

            return [$attribute => [
                'field'      => $field,
                'value'      => $value,
                'attributes' => $field ? $field->storageAttributes($this->request, $field->requestAttribute()) : null, ],
            ];
        });
    }

    /**
     * Sync the MorphMany fields
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     *
     * @return void
     */
    protected function syncMorphManyFields($record)
    {
        foreach ($this->morphManyFields() as $relation => $values) {
            $beforeUpdateAttributes = [];
            $afterUpdateAttributes  = [];

            if ($record->{$relation}()->getModel() instanceof TracksMorphManyModelAttributes) {
                $trackAttributes = (array) $record->{$relation}()->getModel()->trackAttributes();

                foreach ($record->{$relation} as $morphMany) {
                    $beforeUpdateAttributes[] = $morphMany->only($trackAttributes);
                }
            }

            foreach ((array) $values as $attributes) {
                $deleted   = false;
                $deletable = isset($attributes['_delete']);
                $fillable  = Arr::except($attributes, ['_delete', '_track_by']);

                if ($deletable) {
                    $model = $record->{$relation}()->find($attributes['id']);
                    $model->delete();
                    $deleted = true;
                } elseif (isset($attributes['id'])) {
                    $model = tap($record->{$relation}()->find($attributes['id']), function ($instance) use ($fillable) {
                        $instance->fill($fillable)->save();
                    });
                } else {
                    $model = $record->{$relation}()->updateOrCreate($attributes['_track_by'] ?? $fillable, $fillable);
                }

                if ($model instanceof TracksMorphManyModelAttributes) {
                    if ($deleted) {
                        $afterUpdateAttributes[] = collect($trackAttributes)->mapWithKeys(function ($attribute) {
                            return [$attribute => null];
                        });

                        continue;
                    }

                    foreach ($record->{$relation}()->get() as $morphMany) {
                        $afterUpdateAttributes[] = $morphMany->only($trackAttributes);
                    }
                }
            }

            if ($beforeUpdateAttributes != $afterUpdateAttributes &&
                    $record instanceof HandlesChangedMorphManyAttributes) {
                $record->morphManyAtributesUpdated($relation, $afterUpdateAttributes, $beforeUpdateAttributes);
            }
        }
    }
}
