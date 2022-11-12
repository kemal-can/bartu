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

namespace App\Mail;

class ContactAttendsToActivity extends UserAttendsToActivity
{
    /**
     * Provide the defined mailable template placeholders
     *
     * @return \App\Innoclapps\Resources\MailPlaceholders
     */
    public function placeholders()
    {
        return parent::placeholders()->forget([
            'url', 'action_button', 'note',
            'updated_at', 'created_at', 'reminder_minutes_before',
            'owner_assigned_date', 'reminded_at',
        ]);
    }

    /**
     * Provides the mail template default message
     *
     * @return string
     */
    public static function defaultHtmlTemplate()
    {
        return '<p>Hello {{ guest_name }}<br /></p>
                <p>You have been added as a guest of the {{ title }} activity.</p>';
    }

    /**
     * Provides the mail template default subject
     *
     * @return string
     */
    public static function defaultSubject()
    {
        return 'You have been added as guest to activity';
    }
}
