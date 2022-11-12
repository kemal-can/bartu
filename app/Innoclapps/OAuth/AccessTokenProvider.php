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

namespace App\Innoclapps\OAuth;

class AccessTokenProvider
{
    /**
     * Initialize the acess token provider class
     *
     * @param string $token
     * @param string $email
     */
    public function __construct(protected string $token, protected string $email)
    {
    }

    /**
     * Get the access token
     *
     * @return string
     */
    public function getAccessToken() : string
    {
        return $this->token;
    }

    /**
     * Get the token email adress
     *
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }
}
