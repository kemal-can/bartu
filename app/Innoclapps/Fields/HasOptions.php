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

use App\Innoclapps\Resources\Resource;

trait HasOptions
{
    /**
     * Provided options
     *
     * @var mixed
     */
    public $options = [];

    /**
     * Add field options
     *
     * @param array|callable|Illuminate\Support\Collection|App\Innoclapps\Resources\Resource $options
     *
     * @return static
     */
    public function options(mixed $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Resolve the fields options
     *
     * @return array
     */
    public function resolveOptions()
    {
        if ($this->shoulUseZapierOptions()) {
            $options = $this->zapierOptions();
        } else {
            $options = with($this->options, function ($options) {
                if (is_callable($options)) {
                    $options = $options();
                }

                if ($options instanceof Resource) {
                    $options = $options->applyOrder($options->repository())->all();
                }

                return $options;
            });
        }

        return collect($options)->map(function ($label, $value) {
            return isset($label[$this->valueKey]) ? $label : [$this->labelKey => $label, $this->valueKey => $value];
        })->values()->all();
    }

    /**
     * Check whether the Zapier options should be used
     *
     * @return boolean
     */
    protected function shoulUseZapierOptions()
    {
        return request()->isZapier() && method_exists($this, 'zapierOptions');
    }

    /**
    * Field additional meta
    *
    * @return array
    */
    public function meta() : array
    {
        return array_merge([
            'valueKey'           => $this->valueKey,
            'labelKey'           => $this->labelKey,
            'optionsViaResource' => $this->options instanceof Resource ? $this->options->name() : null,
            'options'            => $this->resolveOptions(),
        ], $this->meta);
    }
}
