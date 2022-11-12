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

namespace App\Innoclapps\Updater;

use App\Innoclapps\Models\Patch;
use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Contracts\Repositories\PatchRepository;

class PatchRepositoryEloquent extends AppRepository implements PatchRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Patch::class;
    }

    /**
     * Find patch by token
     *
     * @param string $token
     *
     * @return \App\Innoclapps\Models\Patch|null
     */
    public function findByToken(string $token) : ?Patch
    {
        return $this->findByField('token', $token)->first();
    }

    /**
     * Check whether the given patch token is applied
     *
     * @param string $token
     *
     * @return boolean
     */
    public function isApplied(string $token) : bool
    {
        return ! is_null($this->findByToken($token));
    }
}
