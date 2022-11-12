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

namespace App\Contracts;

use App\Models\Synchronization;

interface SynchronizesViaWebhook
{
    /**
     * Subscribe for changes for the given synchronization
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function watch(Synchronization $synchronization);

    /**
     * Unsubscribe from changes for the given synchronization
     *
     * @param \App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function unwatch(Synchronization $synchronization);
}
