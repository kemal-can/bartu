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

namespace App\Resources\Contact;

use App\Innoclapps\Resources\ResourcefulHandlerWithFields;

class ResourcefulHandler extends ResourcefulHandlerWithFields
{
    /**
     * Handle the resource store action
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store()
    {
        $model = parent::store();

        if ($model->email && (bool) settings('auto_associate_company_to_contact')) {
            $this->request->resource()->repository()->associateCompaniesByEmailDomain($model);
        }

        return $model;
    }
}
