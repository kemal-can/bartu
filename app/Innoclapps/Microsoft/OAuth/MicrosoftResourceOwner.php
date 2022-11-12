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

namespace App\Innoclapps\Microsoft\OAuth;

use App\Innoclapps\OAuth\ResourceOwner;

class MicrosoftResourceOwner extends ResourceOwner
{
    /**
     * Get the resource owner email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'] ?? $this->response['userPrincipalName'];
    }
}
