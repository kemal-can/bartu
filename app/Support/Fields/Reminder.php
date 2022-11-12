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

namespace App\Support\Fields;

use App\Innoclapps\Fields\Field;

class Reminder extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'reminder-field';

    /**
     * Indicates whether to allow the user to cancel the reminder
     *
     * @return static
     */
    public function cancelable()
    {
        $this->rules('nullable');

        return $this->withMeta([__FUNCTION__ => true]);
    }
}
