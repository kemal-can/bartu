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

namespace App\Innoclapps\VoIP;

use Illuminate\Support\Manager;
use App\Innoclapps\VoIP\Clients\Twilio;
use App\Innoclapps\Contracts\VoIP\ReceivesEvents;

class VoIPManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['innoclapps.voip.client'];
    }

    /**
     * Create Twilio VoIP driver
     *
     * @return \App\Innoclapps\VoIP\Clients\Twilio
     */
    public function createTwilioDriver()
    {
        return new Twilio($this->container['config']['innoclapps.services.twilio']);
    }

    /**
     * Check whether the driver receives events
     *
     * @param string|null $driver
     *
     * @return boolean
     */
    public function shouldReceivesEvents($driver = null)
    {
        return $this->driver($driver) instanceof ReceivesEvents;
    }
}
