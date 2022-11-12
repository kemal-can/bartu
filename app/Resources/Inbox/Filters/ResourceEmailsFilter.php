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

namespace App\Resources\Inbox\Filters;

use App\Innoclapps\Filters\Number;
use App\Innoclapps\Filters\HasMany;
use App\Innoclapps\Filters\Operand;

class ResourceEmailsFilter extends HasMany
{
    /**
     * Initialize ResourceEmailsFilter class
     */
    public function __construct()
    {
        parent::__construct('emails', __('mail.emails'));

        $this->setOperands([
            (new Operand('total_unread', __('inbox.unread_count')))->filter(
                Number::make('total_unread')->countableRelation('unreadEmailsForUser')
            ),
        ]);
    }
}
