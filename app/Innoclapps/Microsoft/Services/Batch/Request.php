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

namespace App\Innoclapps\Microsoft\Services\Batch;

class Request
{
    /**
     * @link https://docs.microsoft.com/en-us/graph/known-issues#limit-on-batch-size
     *
     * @var integer
     */
    protected $batchSize = 20;

    /**
     * @var \Microsoft\Graph\Http\GraphCollectionRequest
     */
    protected $graphRequest;

    /**
     * Holds the batch requests
     *
     * @var \App\Innoclapps\Microsoft\Services\Batch\BatchRequests
     */
    protected $requests;

    /**
     * Initialize new Request instance.
     *
     * @param \Microsoft\Graph\Http\GraphCollectionRequest $graphRequest
     * @param \App\Innoclapps\Microsoft\Services\Batch\BatchRequests $requests
     */
    public function __construct($graphRequest, BatchRequests $requests)
    {
        $this->graphRequest = $graphRequest;
        $this->setRequests($requests);
    }

    /**
     * Execute the batch requests
     *
     * @return boolean|array
     */
    public function execute()
    {
        /**
         * When the batch is empty the API throws error: Invalid batch payload format.
         * In this case, we will perform batch request only if there is data
         */
        if ($this->isEmpty()) {
            return false;
        }

        return $this->make();
    }

    /**
     * Make the request
     *
     * @return array
     */
    public function make()
    {
        $responses = [];

        $callback = function ($requests) use (&$responses) {
            $response = $this->graphRequest->attachBody([
                    'requests' => $requests->values()->toArray(),
                ])
                ->execute()->getBody();

            $responses = array_merge($responses, $response['responses']);
        };

        $this->requests->all()
            ->chunk($this->batchSize)
            ->each($callback);

        return $responses;
    }

    /**
     * Check whether there is requests to perform
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->requests->count() === 0;
    }

    /**
     * Set the batch requests
     *
     * @param \App\Innoclapps\Microsoft\Services\Batch\BatchRequests $requests
     *
     * @return static
     */
    public function setRequests(BatchRequests $requests)
    {
        $this->requests = $requests;

        return $this;
    }
}
