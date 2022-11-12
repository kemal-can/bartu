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

class Labels extends Service
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
     * List all available user labels
     *
     * @return array
     */
    public function list()
    {
        $labels         = [];
        $labelsResponse = $this->service->users_labels->listUsersLabels('me');

        if ($labelsResponse->getLabels()) {
            $labels = array_merge($labels, $labelsResponse->getLabels());
        }

        return $labels;
    }

    /**
     * Get user label by id
     *
     * @param string $id
     *
     * @return \Google_Service_Gmail_Label
     */
    public function get($id)
    {
        return $this->service->users_labels->get('me', $id);
    }
}
