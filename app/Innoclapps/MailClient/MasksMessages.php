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

use Illuminate\Support\Collection;

trait MasksMessages
{
    /**
     * Mask given messages into a given class
     *
     * @param array|\Illuminate\Support\Collection $messages
     * @param string $maskIntoClass
     *
     * @return \Illuminate\Support\Collection
     */
    protected function maskMessages($messages, $maskIntoClass)
    {
        if (! $messages) {
            $messages = [];
        }

        if (! $messages instanceof Collection) {
            $messages = collect($messages);
        }

        return $messages->map(function ($message) use ($maskIntoClass) {
            return $this->maskMessage($message, $maskIntoClass);
        });
    }

    /**
     * Mask a given message
     *
     * @param mixed $message
     * @param string $maskIntoClass
     *
     * @return \App\Innoclapps\Contracts\MailClient\MessageInterface
     */
    protected function maskMessage($message, $maskIntoClass)
    {
        return new $maskIntoClass($message);
    }
}
