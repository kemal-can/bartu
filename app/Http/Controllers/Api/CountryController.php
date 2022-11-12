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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Resources\CountryResource;
use App\Innoclapps\Contracts\Repositories\CountryRepository;

class CountryController extends ApiController
{
    /**
     * Initialize new CountryController instance.
     *
     * @param \App\Innoclapps\Contracts\Repositories\CountryRepository $repository
     */
    public function __construct(protected CountryRepository $repository)
    {
    }

    /**
     * List all countries
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        return $this->response(
            CountryResource::collection($this->repository->all())
        );
    }
}
