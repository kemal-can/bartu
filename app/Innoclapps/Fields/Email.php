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

class Email extends Text
{
    /**
     * Input type
     *
     * @var string
     */
    public string $inputType = 'email';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['email', 'nullable'])->prependIcon('Mail')
            ->tapIndexColumn(function ($column) {
                $column->useComponent('table-data-email');
            })->provideSampleValueUsing(fn () => uniqid() . '@example.com');
    }
}
