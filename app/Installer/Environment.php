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

namespace App\Installer;

class Environment
{
    /**
     * Additional .env file variables
     *
     * @var array
     */
    protected array $additional = [];

    /**
     * Initialize new Environment instance.
     *
     * @param string $name
     * @param string $key
     * @param string $identificationKey
     * @param string $url
     * @param string $dbHost
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPassword
     */
    public function __construct(
        protected string $name,
        protected string $key,
        protected string $identificationKey,
        protected string $url,
        protected string $dbHost,
        protected string $dbPort,
        protected string $dbName,
        protected string $dbUser,
        protected string $dbPassword,
    ) {
    }

    /**
     * Add additional variables to the .env file
     *
     * @param array $additional
     *
     * @return static
     */
    public function with(array $additional) : static
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the application name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the application key
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Get the application identification key
     */
    public function getIdentificationKey() : string
    {
        return $this->identificationKey;
    }

    /**
     * Get the application url
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * Get the database hostname
     */
    public function getDbHost() : string
    {
        return $this->dbHost;
    }

    /**
     * Get the database port
     */
    public function getDbPort() : string
    {
        return $this->dbPort;
    }

    /**
     * Get the database name
     */
    public function getDbName() : string
    {
        return $this->dbName;
    }

    /**
     * Get the database user
     */
    public function getDbUser() : string
    {
        return $this->dbUser;
    }

    /**
     * Get the database password
     */
    public function getDbPassword() : string
    {
        return $this->dbPassword;
    }

    /**
     * Get additional .env file variables
     *
     * @return array
     */
    public function getAdditional() : array
    {
        return $this->additional;
    }
}
