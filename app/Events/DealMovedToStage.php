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

use App\Models\Deal;
use App\Models\Stage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DealMovedToStage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new DealMovedToStage instance
     *
     * @param \App\Models\Deal $deal
     * @param \App\Models\Stage $previousStage
     */
    public function __construct(public Deal $deal, public Stage $previousStage)
    {
    }
}
