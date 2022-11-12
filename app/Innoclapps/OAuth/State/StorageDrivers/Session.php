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

namespace App\Innoclapps\OAuth\State\StorageDrivers;

use App\Innoclapps\Contracts\OAuth\StateStorage;
use Illuminate\Support\Facades\Session as Storage;

class Session implements StateStorage
{
    /**
     * The state session key
     *
     * @var string
     */
    protected $key = 'oauth2state';

    /**
     * Get state from storage
     *
     * @return string|null
     */
    public function get() : ?string
    {
        return Storage::get($this->key);
    }

    /**
     * Put state in storage
     *
     * @param string $value
     *
     * @return void
     */
    public function put($value) : void
    {
        Storage::put($this->key, $value);
    }

    /**
     * Check whether there is stored state
     *
     * @return boolean
     */
    public function has() : bool
    {
        return Storage::has($this->key);
    }

    /**
     * Forget the remembered state from storage
     *
     * @return void
     */
    public function forget() : void
    {
        Storage::forget($this->key);
    }
}
