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

namespace App\Innoclapps\VoIP\Events;

use App\Innoclapps\VoIP\Call;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class IncomingCallMissed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new instance of IncomingCallMissed
     *
     * @param \App\Innoclapps\VoIP\Call $call
     */
    public function __construct(public Call $call)
    {
    }
}
