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

namespace App\Innoclapps\MailClient\Compose;

class Message extends AbstractComposer
{
    /**
     * Reply to the message
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function send()
    {
        return $this->client->send();
    }
}
