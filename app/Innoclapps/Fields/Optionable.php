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

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Optionable extends Field
{
    use HasOptions,
        ChangesKeys;

    /**
     * Cached options
     *
     * @var \Illuminate\Support\Collection|null
     */
    protected ?Collection $cachedOptions = null;

    /**
     * Indicates whether the field accept string as value
     *
     * @var boolean
     */
    public bool $acceptLabelAsValue = false;

    /**
     * Accept string value
     *
     * @return static
     */
    public function acceptLabelAsValue()
    {
        $this->acceptLabelAsValue = true;

        $callback = function ($value, $request, $validator, $input) {
            if ($this->isMultiOptionable()) {
                $value = $this->parseValueAsLabelViaMultiOptionable($value);
            } elseif (! is_numeric($value) && ! is_null($value)) {
                $value = $this->parseValueAsLabelViaOptionable($value, $input);
            }

            return $value;
        };

        return $this->prepareForValidation($callback);
    }

    /**
    * Get the sample value for the field
    *
    * @return string
    */
    public function sampleValue()
    {
        if (is_callable($this->sampleValueCallback)) {
            return call_user_func_array($this->sampleValueCallback, [$this->attribute]);
        }

        return $this->resolveOptions()[0][
           $this->acceptLabelAsValue ? $this->labelKey : $this->valueKey
        ] ?? '';
    }

    /**
     * Get option by given label
     *
     * @param string $label
     *
     * @return mixed
     */
    public function optionByLabel($label)
    {
        return $this->getCachedOptionsCollection()->firstWhere(
            $this->labelKey,
            $label
        );
    }

    /**
     * Get cached options collection
     *
     * When importing data, the label as value function will be called
     * multiple times, we don't want all the queries executed multiple times
     * from the fields which are providing options via repository
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCachedOptionsCollection() : Collection
    {
        if (! $this->cachedOptions) {
            $this->cachedOptions = collect($this->resolveOptions());
        }

        return $this->cachedOptions;
    }

    /**
     * Clear the cached options collection
     *
     * @return static
     */
    public function clearCachedOptionsCollection() : static
    {
        $this->cachedOptions = null;

        return $this;
    }

    /**
     * Get the field value when label is provided
     *
     * @param string $value
     * @param array $input
     *
     * @return mixed
     */
    protected function parseValueAsLabelViaOptionable($value, $input)
    {
        $options = $this->getCachedOptionsCollection();

        return $options->firstWhere($this->labelKey, $value)[$this->valueKey] ?? null;
    }

    /**
     * Get the field value when label is provided for multi optionable fields
     *
     * @param array|string $value
     *
     * @return array
     */
    protected function parseValueAsLabelViaMultiOptionable(array|string|null $value) : array
    {
        if (is_null($value)) {
            return [];
        }

        $options = $this->getCachedOptionsCollection();

        if (is_string($value)) {
            $value = Str::of($value)->explode(',')->map(fn ($content) => trim($content))->all();
        } elseif (is_int($value)) {
            $value = [$value];
        }

        foreach ($value as $key => $label) {
            if (! is_numeric($label)) {
                if ($option = $options->firstWhere($this->labelKey, $label)) {
                    $value[$key] = $option[$this->valueKey] ?? null;
                } else {
                    // Invalid key provided
                    unset($value[$key]);
                }
            }
        }

        return array_filter(
            array_values($value)
        );
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'acceptLabelAsValue' => $this->acceptLabelAsValue,
        ]);
    }
}
