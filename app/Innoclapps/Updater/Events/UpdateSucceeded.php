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

namespace App\Innoclapps\Updater\Events;

use App\Innoclapps\Updater\Release;

class UpdateSucceeded
{
    /**
     * Initialize new UpdateSucceeded instance.
     *
     * @param \App\Innoclapps\Updater\Release $release
     */
    public function __construct(protected Release $release)
    {
    }

    /**
     * Get the new version.
     *
     * @return string
     */
    public function getVersionUpdatedTo() : string
    {
        return $this->release->getVersion();
    }
}
