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

namespace App\Innoclapps\Settings\Stores;

use Illuminate\Support\Arr;
use Illuminate\Redis\RedisManager;

/**
 * @codeCoverageIgnore
 * NOT USED YET
 */
class RedisStore extends AbstractStore
{
    /**
     * The redis manager.
     *
     * @var \Illuminate\Redis\RedisManager
     */
    protected $manager;

    /**
     * Fire the post options to customize the store.
     *
     * @param array $options
     */
    protected function postOptions(array $options)
    {
        $this->manager = new RedisManager(
            $this->app,
            Arr::pull($options, 'client', 'predis'),
            $options
        );
    }

    /**
     * Read the data from the store.
     *
     * @return array
     */
    protected function read() : array
    {
        $data = $this->command('get', ['settings']);

        return is_string($data) ? json_decode($data, true) : [];
    }

    /**
     * Write the data into the store.
     *
     * @param array $data
     */
    protected function write(array $data) : void
    {
        $this->command('set', ['settings', json_encode($data)]);
    }

    /**
     * Get a Redis connection by name.
     *
     * @param string|null $name
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function connection(?string $name = null)
    {
        return $this->manager->connection($name);
    }

    /**
     * Run a command against the Redis database.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    protected function command(string $method, array $parameters = [])
    {
        return $this->connection()->command($method, $parameters);
    }
}
