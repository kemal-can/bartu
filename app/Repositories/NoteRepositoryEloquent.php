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

use App\Models\Note;
use App\Support\PendingMention;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Repository\AppRepository;
use App\Support\Concerns\CreatesFollowUpTask;
use App\Contracts\Repositories\NoteRepository;

class NoteRepositoryEloquent extends AppRepository implements NoteRepository
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
        return Note::class;
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

        $note = parent::create($attributes);

        $this->notifyMentionedUsers(
            $mention,
            $note,
            $attributes['via_resource'],
            $attributes['via_resource_id']
        );

        // Handle create follow up task
        if ($this->shouldCreateFollowUpTask($attributes)) {
            $this->createFollowUpTask(
                $attributes['task_date'],
                $attributes['via_resource'],
                $attributes['via_resource_id'],
                ['note' => __('note.follow_up_task_body', [
                    'content' => $note->body,
                ])]
            );
        }

        return $note;
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

        $note = parent::update($attributes, $id);

        $this->notifyMentionedUsers(
            $mention,
            $note,
            $attributes['via_resource'],
            $attributes['via_resource_id']
        );

        return $note;
    }

    /**
    * Notify the mentioned users for the given mention
    *
    * @param \App\Support\PendingMention $mention
    * @param \App\Models\Note $note
    * @param string $viaResource
    * @param int $viaResourceId
    *
    * @return void
    */
    protected function notifyMentionedUsers(
        PendingMention $mention,
        Note $note,
        string $viaResource,
        string $viaResourceId,
    ) {
        $intermediate = Innoclapps::resourceByName($viaResource)->repository()->find($viaResourceId);

        $mention->setUrl($intermediate->path)->withUrlQueryParameter([
            'section'    => $note->resource()->name(),
            'resourceId' => $note->getKey(),
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
     * Purge the note data
     *
     * @param \App\Models\Note $note
     *
     * @return void
     */
    protected function purge($note)
    {
        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $note->{$relation}()->withTrashed()->detach();
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
        ];
    }
}
