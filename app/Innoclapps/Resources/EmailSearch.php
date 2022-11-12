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

class EmailSearch extends GlobalSearch
{
    /**
     * Provide the model data for the response
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \App\Innoclapps\Resources\resource $resource
     *
     * @return array
     */
    protected function data($model, $resource) : array
    {
        return [
            'id'           => $model->getKey(),
            'address'      => $model->email,
            'name'         => $model->display_name,
            'path'         => $model->path,
            'resourceName' => $resource->name(),
        ];
    }
}
