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

namespace App\Innoclapps\Contracts\OAuth;

interface StateStorage
{
    /**
     * Get state from storage
     *
     * @return string|null
     */
    public function get() : ?string;

    /**
     * Put state in storage
     *
     * @param string $value
     *
     * @return void
     */
    public function put($value) : void;

    /**
     * Check whether there is stored state
     *
     * @return boolean
     */
    public function has() : bool;

    /**
     * Forget the remembered state from storage
     *
     * @return void
     */
    public function forget() : void;
}
