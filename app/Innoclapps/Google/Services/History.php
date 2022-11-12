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

namespace App\Innoclapps\Google\Services;

use Google_Service_Gmail;

class History extends Service
{
    /**
     * Initialize new Service instance
     *
     * @param \Google_Client $client
     */
    public function __construct($client)
    {
        parent::__construct($client, Google_Service_Gmail::class);
    }

    /**
     * https://developers.google.com/gmail/api/v1/reference/users/history/list
     *
     * Get the Gmail account history
     *
     * @param array $params Additional params for the request
     *
     * @return \Google_Service_Gmail_History
     */
    public function get($params = [])
    {
        return $this->service->users_history->listUsersHistory('me', $params);
    }
}
