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

namespace App\MailClient\Exceptions;

class SyncFolderTimeoutException extends \RuntimeException
{
    /**
     * @param string $account Account email
     * @param string $folderName Email account folder full name
     */
    public function __construct($account, $folderName)
    {
        parent::__construct(
            sprintf(
                'Exit because of email account "%s" folder "%s" sync exceeded max save time per batch.',
                $account,
                $folderName
            )
        );
    }
}
