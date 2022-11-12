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

namespace App\Innoclapps\Google\OAuth;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GoogleUser;

class GoogleProvider extends Google
{
    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param \League\OAuth2\Client\Token\AccessToken $token
     *
     * @return \League\OAuth2\Client\Provider\GoogleUser
     */
    protected function createResourceOwner(array $response, AccessToken $token) : GoogleUser
    {
        return new GoogleResourceOwner($response);
    }
}
