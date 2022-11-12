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

namespace App\Listeners;

use App\Events\EmailAccountMessageCreated;
use App\Contracts\Repositories\ContactRepository;

class AttachEmailAccountMessageToContact
{
    /**
     * Initialize new AttachEmailAccountMessageToContact instance.
     *
     * @param \App\Contracts\Repositories\ContactRepository $repository
     */
    public function __construct(protected ContactRepository $repository)
    {
    }

    /**
     * When a message is created, try to associate the
     * message with the actual contact if exists in database
     *
     * @param object $event
     * @return void
     */
    public function handle(EmailAccountMessageCreated $event)
    {
        if (! $event->message->from) {
            return;
        }

        if (! $contact = $this->repository->findByEmail($event->message->from->address)) {
            return;
        }

        if ($this->repository->whereHas('emails', fn ($query) => $query->where('id', $event->message->id))->count() === 0) {
            $this->repository->attach($contact->id, 'emails', $event->message->id);
        }
    }
}
