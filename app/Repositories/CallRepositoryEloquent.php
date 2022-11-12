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

use App\Models\Call;
use App\Support\PendingMention;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Repository\AppRepository;
use App\Support\Concerns\CreatesFollowUpTask;
use App\Contracts\Repositories\CallRepository;

class CallRepositoryEloquent extends AppRepository implements CallRepository
{
    use CreatesFollowUpTask;

    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'body' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Call::class;
    }

    /**
    * Save a new entity in repository
    *
    * @param array $attributes
    *
    * @return mixed
    */
    public function create(array $attributes)
    {
        $mention            = new PendingMention($attributes['body']);
        $attributes['body'] = $mention->getUpdatedText();

        $call = parent::create($attributes);

        $this->notifyMentionedUsers(
            $mention,
            $call,
            $attributes['via_resource'],
            $attributes['via_resource_id']
        );

        // Handle create follow up task
        if ($this->shouldCreateFollowUpTask($attributes)) {
            $this->createFollowUpTask(
                $attributes['task_date'],
                $attributes['via_resource'],
                $attributes['via_resource_id'],
                ['note' => __('call.follow_up_task_body', [
                    'content' => $call->body,
                ])]
            );
        }

        return $call;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $mention            = new PendingMention($attributes['body']);
        $attributes['body'] = $mention->getUpdatedText();

        $call = parent::update($attributes, $id);

        $this->notifyMentionedUsers(
            $mention,
            $call,
            $attributes['via_resource'],
            $attributes['via_resource_id']
        );

        return $call;
    }

    /**
    * Notify the mentioned users for the given mention
    *
    * @param \App\Support\PendingMention $mention
    * @param \App\Models\Call $call
    * @param string $viaResource
    * @param int $viaResourceId
    *
    * @return void
    */
    protected function notifyMentionedUsers(PendingMention $mention, $call, $viaResource, $viaResourceId)
    {
        $intermediate = Innoclapps::resourceByName($viaResource)->repository()->find($viaResourceId);

        $mention->setUrl($intermediate->path)->withUrlQueryParameter([
            'section'    => $call->resource()->name(),
            'resourceId' => $call->getKey(),
        ])->notify();
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
        static::deleting(fn ($model, $repository) => $repository->purge($model));
    }

    /**
     * Purge the call data
     *
     * @param \App\Models\Call $call
     *
     * @return void
     */
    protected function purge($call)
    {
        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $call->{$relation}()->withTrashed()->detach();
        }
    }

    /**
     * The relations that are required for the responsee
     *
     * @return array
     */
    protected function eagerLoad()
    {
        $this->withCount(['comments']);

        return [
            'companies.nextActivity',
            'contacts.nextActivity',
            'deals.nextActivity',
            'user',
            'outcome',
        ];
    }
}
