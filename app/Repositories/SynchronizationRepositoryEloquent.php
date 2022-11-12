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

namespace App\Repositories;

use Carbon\Carbon;
use App\Enums\SyncState;
use App\Models\Synchronization;
use App\Contracts\SynchronizesViaWebhook;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\SynchronizationRepository;

class SynchronizationRepositoryEloquent extends AppRepository implements SynchronizationRepository
{
    /**
    * Specify Model class name
    *
    * @return string
    */
    public static function model()
    {
        return Synchronization::class;
    }

    /**
     * Apply where query where oauth account does not require authentication
     *
     * @return static
     */
    public function withoutOAuthAuthenticationRequired() : static
    {
        return $this->whereHas('synchronizable', function ($query) {
            return $query->whereHas('oAuthAccount', function ($query) {
                return $query->where('requires_auth', false);
            });
        });
    }

    /**
     * Mark the synchronization as webhook synchronizable
     *
     * @param string $resourceId
     * @param string|\Carbon\Carbon $expiresAt
     * @param integer|\App\Models\Synchronization $synchronization
     *
     * @return \App\Models\Synchronization
     */
    public function markAsWebhookSynchronizable(string $resourceId, string|Carbon $expiresAt, int|Synchronization $synchronization)
    {
        $id = is_int($synchronization) ? $synchronization : $synchronization->getKey();

        return $this->update([
            'resource_id' => $resourceId,
            'expires_at'  => $expiresAt instanceof Carbon ? $expiresAt :Carbon::parse($expiresAt),
        ], $id);
    }

    /**
     * Unmark the synchronization as webhook synchronizable
     *
     * @param integer|\App\Models\Synchronization $synchronization
     *
     * @return \App\Models\Synchronization
     */
    public function unmarkAsWebhookSynchronizable(int|Synchronization $synchronization)
    {
        $id = is_int($synchronization) ? $synchronization : $synchronization->getKey();

        return $this->update([
            'resource_id' => null,
            'expires_at'  => null,
        ], $id);
    }

    /**
     * Set the sync state
     *
     * @param int|Synchronization $synchronization
     * @param \App\Enums\SyncState $state
     * @param null|string $comment
     *
     * @return void
     */
    public function setSyncState(int|Synchronization $synchronization, SyncState $state, ?string $comment = null)
    {
        $id = is_int($synchronization) ? $synchronization : $synchronization->getKey();

        $this->update([
            'sync_state'         => $state,
            'sync_state_comment' => $comment,
        ], $id);
    }

    /**
     * Enable synchronization
     *
     * @param int|\App\Models\Synchronization $synchronization
     *
     * @return void
     */
    public function enableSync(int|Synchronization $synchronization)
    {
        $model = is_int($synchronization) ? $this->find($synchronization) : $synchronization;

        // When enabling synchronization, we will try again to re-configure the webhook
        // and catch any URL related errors, if any errors, the sync won't be enabled
        if ($model->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
            $model->refreshWebhook();
        }

        $this->setSyncState($model, SyncState::ENABLED);
    }

    /**
     * Disable synchronization
     *
     * @param int|\App\Models\Synchronization $synchronization
     * @param string|null $comment
     *
     * @return void
     */
    public function disableSync(int|Synchronization $synchronization, ?string $comment = null)
    {
        $model = is_int($synchronization) ? $this->find($synchronization) : $synchronization;

        if ($model->isSynchronizingViaWebhook()) {
            $model->stopListeningForChanges();
        }

        $this->update(['token' => null], $model->getKey());
        $this->setSyncState($model, SyncState::DISABLED, $comment);
    }

    /**
     * Stop synchronization
     *
     * @param int|\App\Models\Synchronization $synchronization
     * @param string|null $comment
     *
     * @return void
     */
    public function stopSync(int|Synchronization $synchronization, ?string $comment = null)
    {
        $model = is_int($synchronization) ? $this->find($synchronization) : $synchronization;

        if ($model->isSynchronizingViaWebhook()) {
            $model->stopListeningForChanges();
        }

        $this->update(['token' => null], $model->getKey());
        $this->setSyncState($model, SyncState::STOPPED, $comment);
    }

    /**
     * Get models for periodic synchronizations
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getForPeriodicSynchronizations()
    {
        return $this->withoutOAuthAuthenticationRequired()
            ->with('synchronizable')
            ->findWhere([
                ['resource_id', '=', null], ['sync_state', '=', SyncState::ENABLED],
            ]);
    }
}
