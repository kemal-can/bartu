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

namespace App\Innoclapps\MailClient;

class FolderIdentifier
{
    /**
     * Initialize new FolderIdentifier class
     *
     * @param string $key
     * @param mixed $value
     */
    public function __construct(public $key, public $value)
    {
    }
}
