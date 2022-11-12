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

namespace App\Innoclapps\Microsoft\Services\Batch;

class BatchPatchRequest extends BatchRequest
{
    /**
     * Initialize new BatchPatchRequest instance.
     *
     * @param string $url
     * @param array $body
     */
    public function __construct($url, $body = [])
    {
        parent::__construct($url, $body);
        $this->asJson();
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return 'PATCH';
    }
}
