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

namespace App\Resources\Deal;

use App\Innoclapps\Resources\Import;

class DealImport extends Import
{
    /**
     * Map single rows keys with the actual field attributes
     *
     * @see mapRowsKeysWithActualFieldAttribute
     *
     * @param array $row
     *
     * @return array
     */
    public function map($row) : array
    {
        if (request()->missing('pipeline_id')) {
            throw new \Exception('Pipeline ID must be provided.');
        }

        $row = parent::map($row);

        $row['pipeline_id'] = request()->pipeline_id;

        return $row;
    }
}
