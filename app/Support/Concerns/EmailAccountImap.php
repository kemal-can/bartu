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

namespace App\Support\Concerns;

use App\Innoclapps\MailClient\Imap\SmtpConfig;
use App\Innoclapps\MailClient\Imap\Config as ImapConfig;

trait EmailAccountImap
{
    /**
     * Get the Imap client configuration
     *
     * @return \App\Innoclapps\MailClient\Imap\Config
     */
    public function getImapConfig() : ImapConfig
    {
        return new ImapConfig(
            $this->imap_server,
            $this->imap_port,
            $this->imap_encryption,
            $this->email,
            $this->validate_cert,
            $this->username,
            $this->password
        );
    }

    /**
     * Get the Smtp client configuration
     *
     * @return \App\Innoclapps\MailClient\Imap\SmtpConfig
     */
    public function getSmtpConfig() : SmtpConfig
    {
        return new SmtpConfig(
            $this->smtp_server,
            $this->smtp_port,
            $this->smtp_encryption,
            $this->email,
            $this->validate_cert,
            $this->username,
            $this->password
        );
    }
}
