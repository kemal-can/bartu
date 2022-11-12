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

namespace App\Innoclapps\Contracts\Fields;

interface HandlesChangedMorphManyAttributes
{
    /**
     * Handle the attributes updated event
     *
     * @param array $new
     * @param array $old
     *
     * @return void
     */
    public function morphManyAtributesUpdated($relationName, $new, $old);
}
