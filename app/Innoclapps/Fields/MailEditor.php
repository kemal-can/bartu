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

class MailEditor extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'mail-editor-field';

    /**
     * Resolve the field value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function resolve($model)
    {
        return clean(parent::resolve($model));
    }
}
