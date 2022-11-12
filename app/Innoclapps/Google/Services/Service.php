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

namespace App\Innoclapps\Google\Services;

use Google_Service;

class Service
{
    /**
     * @var \Google_Client
     */
    protected $client;

    /**
     * @var \Google_Service
     */
    protected $service;

    /**
     * Initialize new Service instance
     *
     * @param \Google_Client $client
     * @param string|\Google_Service $service
     * @param mixed $params
     */
    public function __construct($client, $service, ...$params)
    {
        $this->client  = $client;
        $this->service = ! $service instanceof Google_Service ?
            new $service($this->client, ...$params) :
            $service;
    }

    /**
     * Dynamically access the service
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->service->{$key};
    }
}
