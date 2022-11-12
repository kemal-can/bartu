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

use App\Innoclapps\Models\OAuthAccount;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\OAuthAccountRepository;

class OAuthAccountRepositoryEloquent extends AppRepository implements OAuthAccountRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return OAuthAccount::class;
    }

    /**
     * Set that this account requires authentication
     *
     * @param int $id
     * @param boolean $value
     */
    public function setRequiresAuthentication($id, $value = true)
    {
        $this->update(['requires_auth' => $value], $id);
    }
}
