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

namespace App\Innoclapps\Contracts\Resources;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;

interface Tableable
{
    /**
     * Provide the resource table class
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function table($repository, Request $request) : Table;
}
