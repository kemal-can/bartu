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

namespace App\Innoclapps\OAuth\Events;

use Illuminate\Queue\SerializesModels;
use App\Innoclapps\Models\OAuthAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OAuthAccountConnected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new instance of OAuthAccountConnected
     *
     * @param \App\Innoclapps\Models\OAuthAccount $account
     */
    public function __construct(public OAuthAccount $account)
    {
    }
}
