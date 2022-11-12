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

namespace App\Innoclapps\Resources\Http;

use App\Innoclapps\Criteria\OnlyTrashedCriteria;

class TrashedResourcefulRequest extends ResourcefulRequest
{
    /**
     * Get the resource record for the current request
     *
     * @return int
     */
    public function record()
    {
        if (! $this->record) {
            $this->record = $this->resource()
                ->repository()
                ->pushCriteria(OnlyTrashedCriteria::class)
                ->find($this->resourceId());
        }

        return $this->record;
    }
}
