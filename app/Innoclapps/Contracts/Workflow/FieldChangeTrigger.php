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

namespace App\Innoclapps\Contracts\Workflow;

interface FieldChangeTrigger
{
    /**
     * The field to track changes on
     *
     * @return string
     */
    public static function field() : string;

    /**
     * Provide the change field
     *
     * @return \App\Innoclapps\Fields\Field
     */
    public static function changeField();
}
