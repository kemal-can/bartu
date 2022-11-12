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

use App\Enums\DealStatus;
use Illuminate\Http\Request;
use App\Http\Resources\DealResource;
use App\Http\Controllers\ApiController;
use App\Contracts\Repositories\DealRepository;

class DealStatusController extends ApiController
{
    /**
     * Initialize new DealStatusController instance
     *
     * @param \App\Contracts\Repositories\DealRepository $repository
     */
    public function __construct(protected DealRepository $repository)
    {
    }

    /**
     * Change the deal status
     *
     * @param int $id
     * @param string $status
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($id, $status, Request $request)
    {
        $this->authorize('update', $deal = $this->repository->find($id));

        // User must unmark the deal as open when the deal status is won or lost in order to change any further statuses
        abort_if(
            ($deal->status === DealStatus::lost || $deal->status === DealStatus::won) && $status !== DealStatus::open->name,
            409,
            'The deal first must be marked as open in order to apply the ' . $status . ' status.'
        );

        $request->validate(['lost_reason' => 'sometimes|nullable|string|max:191']);

        $deal = $this->repository->changeStatus(DealStatus::find($status), $deal, $request->lost_reason);

        return new DealResource(
            $deal->resource()->displayQuery()->find($deal->id)
        );
    }
}
