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

use App\Models\Team;
use App\Innoclapps\Repository\AppRepository;
use App\Contracts\Repositories\TeamRepository;

class TeamRepositoryEloquent extends AppRepository implements TeamRepository
{
    /**
      * Save a new entity in repository
      *
      * @param array $attributes
      *
      * @return \Illuminate\Database\Eloquent\Model
      */
    public function create(array $attributes)
    {
        $team = parent::create($attributes);

        $this->attach($team->id, 'users', $attributes['members'] ?? []);

        $team->load('users');

        return $team;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id)
    {
        $team = parent::update($attributes, $id);

        if (isset($attributes['members'])) {
            $this->sync($team->id, 'users', (array) $attributes['members']);
        }

        $team->load('users');

        return $team;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param mixed $id
     *
     * @return boolean|array
     */
    public function delete($id)
    {
        $team = $this->find($id);

        $team->users()->detach();

        $team->load('visibilityDependents.group');
        $team->visibilityDependents->each(function ($model) {
            $model->group->teams()->detach();
            $model->group->users()->detach();
        });

        return parent::delete($team);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Team::class;
    }
}
