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

namespace App\Innoclapps\Resources\Http;

class UpdateResourceRequest extends ResourcefulRequest
{
    /**
     * Get the fields for the current request
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function fields()
    {
        $this->resource()->setModel($this->record());

        return $this->resource()->resolveUpdateFields();
    }
}
