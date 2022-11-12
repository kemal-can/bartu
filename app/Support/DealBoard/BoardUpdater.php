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

namespace App\Support\DealBoard;

use Batch;
use App\Models\User;
use App\Events\DealMovedToStage;
use Illuminate\Support\Collection;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;

class BoardUpdater
{
    /**
     * Caches the workable deals
     * @see  deals method
     *
     * @var \Illuminate\Support\Collection
     */
    protected $deals;

    /**
     * Stages cache
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $stages;

    /**
     * The payload data
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $data;

    /**
     * Initialize new BoardUpdater instance.
     *
     * @param array $data
     * @param \App\Contracts\Repositories\DealRepository $repository
     * @param \App\Models\User $user
     */
    public function __construct(array $data, protected DealRepository $repository, protected User $user)
    {
        $this->data   = collect($data);
        $this->stages = resolve(StageRepository::class)->get();
    }

    /**
     * Performs the update
     *
     * @return void
     */
    public function perform() : void
    {
        // We will map the appropriate data for the Batch
        // so we can perform the update without any injected fields
        tap($this->onlyAuthorizedDeals()->map(function ($deal) {
            return [
                'id'           => (int) $deal['id'],
                'stage_id'     => $deal['stage_id'],
                'swatch_color' => $deal['swatch_color'],
                'board_order'  => $deal['board_order'],
            ];
        })->all(), function ($data) {
            $this->triggerMovedToStageEventIfNeeded($data);
            $this->update($data);
        });
    }

    /**
     * Update the deals from the payload
     *
     * If we change this method to not perform the update via batch,
     * check the DealObserver because in the updated event the the LogDealMovedToStageActivity
     * listener is triggered too
     *
     * @param array $data
     *
     * @return void
     */
    protected function update($data)
    {
        $this->fireModelsEvent('updating', $data);
        Batch::update($this->repository->getModel(), $data);
        $this->fireModelsEvent('updated', $data);
    }

    /**
     * Get the deals based on the id's provided in the payload|data
     *
     * @return \Illuminate\Support\Collection
     */
    protected function deals()
    {
        if ($this->deals) {
            return $this->deals;
        }

        return $this->deals = $this->repository->with(['pipeline', 'user'])->findMany(
            $this->data->pluck('id')->all()
        );
    }

    /**
     * Trigger the deal moved to stage event if needed
     *
     * @param \Illuminate\Support\Collection $deals
     *
     * @return void
     */
    protected function triggerMovedToStageEventIfNeeded($deals)
    {
        foreach ($deals as $data) {
            $deal = $this->deals()->find($data['id']);

            if ((int) $deal->stage_id !== (int) $data['stage_id']) {
                $oldStage = $this->stages->find($deal->stage_id);

                // Update with the new stage data
                $deal->stage    = $this->stages->find($data['stage_id']);
                $deal->stage_id = $data['stage_id'];

                event(new DealMovedToStage($deal, $oldStage));
            }
        }
    }

    /**
     * Fire model events
     *
     * @param string $event
     * @param array $data
     *
     * @return void
     */
    protected function fireModelsEvent($event, $data)
    {
        foreach ($data as $attributes) {
            $deal = $this->deals()->find($attributes['id']);
            if ($event === 'updating') {
                $deal->forceFill($attributes);
            }

            $deal->getEventDispatcher()->dispatch("eloquent.{$event}: " . $deal::class, $deal);
        }
    }

    /**
     * Remove any deals which the user is not authorized to update
     *
     * @return \Illuminate\Support\Collection
     */
    protected function onlyAuthorizedDeals()
    {
        return $this->data->reject(function ($data) {
            return $this->user->cant('update', $this->deals()->find($data['id']));
        });
    }
}
