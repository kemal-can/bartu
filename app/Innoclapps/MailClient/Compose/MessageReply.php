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

use App\Innoclapps\MailClient\Client;
use App\Innoclapps\MailClient\FolderIdentifier;

class MessageReply extends AbstractComposer
{
    /**
     * Holds the message ID the reply is intended for
     *
     * @var string|int
     */
    protected string|int $id;

    /**
     * Holds the folder the message belongs to
     *
     * @var \App\Innoclapps\MailClient\FolderIdentifier
     */
    protected FolderIdentifier $folder;

    /**
     * Create new MessageReply instance
     *
     * @param \App\Innoclapps\MailClient\Client $client
     * @param string|int $remoteId
     * @param \App\Innoclapps\MailClient\FolderIdentifier $folder
     * @param \App\Innoclapps\MailClient\FolderIdentifier|null $sentFolder
     */
    public function __construct(Client $client, string|int $remoteId, FolderIdentifier $folder, ?FolderIdentifier $sentFolder = null)
    {
        parent::__construct($client, $sentFolder);

        $this->id     = $remoteId;
        $this->folder = $folder;
    }

    /**
     * Reply to the message
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    public function send()
    {
        return $this->client->reply($this->id, $this->folder);
    }
}
