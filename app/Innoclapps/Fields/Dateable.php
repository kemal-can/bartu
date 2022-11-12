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

trait Dateable
{
    /**
     * Resolve the field value for export.
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return string
     */
    public function resolveForExport($model)
    {
        return $model->{$this->attribute};
    }

    /**
     * Mark the field as clearable
     *
     * @return static
     */
    public function clearable() : static
    {
        $this->withMeta(['attributes' => ['clearable' => true]]);

        return $this;
    }
}
