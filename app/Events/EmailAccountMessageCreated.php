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

namespace App\Events;

use App\Models\EmailAccountMessage;
use Illuminate\Queue\SerializesModels;
use App\Innoclapps\Contracts\MailClient\MessageInterface;

class EmailAccountMessageCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\EmailAccountMessage $message
     * @param \App\Innoclapps\Contracts\MailClient\MessageInterface $message
     * @return void
     */
    public function __construct(public EmailAccountMessage $message, public MessageInterface $remoteMessage)
    {
    }
}
