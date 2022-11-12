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

namespace App\Workflows\Actions;

use App\Innoclapps\Fields\Select;
use App\Innoclapps\Facades\Innoclapps;

class ResourcesSendEmailToField extends Select
{
    /**
     * Initialize new ResourcesSendEmailToField field
     */
    public function __construct()
    {
        parent::__construct('to');

        $this->rules('required')->withMeta([
            'attributes' => ['placeholder' => __('mail.workflows.fields.to'),
        ], ]);
    }

    /**
     * Get the available resources from the field
     *
     * @return array
     */
    public function getToResources()
    {
        return collect($this->options)->mapWithKeys(function ($option, $key) {
            return [$key => Innoclapps::resourceByName($option['resource'])];
        })->all();
    }

    /**
     * Resolve the field options
     *
     * @return array
     */
    public function resolveOptions()
    {
        return collect(parent::resolveOptions())->map(function ($option) {
            return [
                $this->labelKey => $option['label']['label'],
                $this->valueKey => $option['value'],
            ];
        })->all();
    }
}
