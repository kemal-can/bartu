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

namespace App\Innoclapps\MailClient\Imap;

class Config
{
    public function __construct(
        protected string $host,
        protected int $port,
        protected ?string $encryption,
        protected string $email,
        protected bool $validateCertificate,
        protected ?string $username,
        protected string $password,
    ) {
    }

    /**
     * Get the connection server/host
     *
     * @return string
     */
    public function host() : string
    {
        return $this->host;
    }

    /**
     * Get the connection port
     *
     * @return int
     */
    public function port() : int
    {
        return $this->port;
    }

    /**
     * Get the connection encryption type
     *
     * @return string|null ssl|tls|starttls
     */
    public function encryption() : ?string
    {
        return $this->encryption ?? null;
    }

    /**
     * Get the connection email address
     *
     * @return string
     */
    public function email() : string
    {
        return $this->email;
    }

    /**
     * Whether to validate the certificate
     *
     * @return boolean
     */
    public function validateCertificate() : bool
    {
        return $this->validateCertificate;
    }

    /**
     * Get connection username in case using different username
     * then the email address
     *
     * @return string|null
     */
    public function username() : ?string
    {
        return $this->username;
    }

    /**
     * Get the connection password
     *
     * @return string
     */
    public function password() : string
    {
        return $this->password;
    }
}
