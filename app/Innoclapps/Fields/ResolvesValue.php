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

namespace App\Innoclapps\Fields;

trait ResolvesValue
{
    /**
     * Resolve field value callback
     *
     * @var callable|null
     */
    public $resolveCallback;

    /**
     * Display callback
     *
     * @var callable|null
     */
    public $displayCallback;

    /**
     * Import callback
     *
     * @var callable|null
     */
    public $importCallback;

    /**
     * Import sample callback
     *
     * @var callable|null
     */
    public $importSampleValueCallback;

    /**
     * Sample value callback
     *
     * @var callable|null
     */
    public $sampleValueCallback;

    /**
     * Resolve the actual field value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function resolve($model)
    {
        if (is_callable($this->resolveCallback)) {
            return call_user_func_array($this->resolveCallback, [$model, $this->attribute]);
        }

        return $model->{$this->attribute};
    }

    /**
     * Resolve the displayable field value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function resolveForDisplay($model)
    {
        if (is_callable($this->displayCallback)) {
            return call_user_func_array($this->displayCallback, [$model, $this->resolve($model)]);
        }

        return $this->resolve($model);
    }

    /**
     * Resolve the field value for export
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return $this->resolveForDisplay($model);
    }

    /**
     * Resolve the field value for import
     *
     * @param string|null $value
     * @param array $row
     * @param array $original
     *
     * @return array|null
     */
    public function resolveForImport($value, $row, $original)
    {
        if (is_callable($this->importCallback)) {
            return call_user_func_array($this->importCallback, [$value, $row, $original, $this]);
        }

        return [$this->attribute => $value];
    }

    /**
     * Get the sample value for import
     *
     * @return mixed
     */
    public function sampleValueForImport()
    {
        if (is_callable($this->importSampleValueCallback)) {
            return call_user_func_array($this->importSampleValueCallback, [$this->attribute]);
        }

        return $this->sampleValue();
    }

    /**
      * Get the sample value for the field
      *
      * @return mixed
      */
    public function sampleValue()
    {
        if (is_callable($this->sampleValueCallback)) {
            return call_user_func_array($this->sampleValueCallback, [$this->attribute]);
        }

        return 'Sample Data';
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        return [$this->attribute => $this->resolve($model)];
    }

    /**
     * Add custom value resolver
     *
     * @param callable $resolveCallback
     *
     * @return static
     */
    public function resolveUsing(callable $resolveCallback)
    {
        $this->resolveCallback = $resolveCallback;

        return $this;
    }

    /**
     * Add custom display resolver
     *
     * @param callable $displayCallback
     *
     * @return static
     */
    public function displayUsing(callable $displayCallback)
    {
        $this->displayCallback = $displayCallback;

        return $this;
    }

    /**
     * Add custom import value resolver
     *
     * @param callable $importCallback
     *
     * @return static
     */
    public function importUsing(callable $importCallback)
    {
        $this->importCallback = $importCallback;

        return $this;
    }

    /**
     * Add custom import sample value resolver
     *
     * @param callable $callback
     *
     * @return static
     */
    public function provideImportValueSampleUsing(callable $callback)
    {
        $this->importSampleValueCallback = $callback;

        return $this;
    }

    /**
     * Add custom sample value resolver
     *
     * @param callable $callback
     *
     * @return static
     */
    public function provideSampleValueUsing(callable $callback)
    {
        $this->sampleValueCallback = $callback;

        return $this;
    }
}
