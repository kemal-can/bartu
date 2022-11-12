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

namespace App\Support\Filters;

use App\Innoclapps\Filters\Select;
use App\Criteria\Team\OwnTeamsCriteria;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Repositories\TeamRepository;

class ResourceUserTeamFilter extends Select
{
    /**
     * Create new ResourceUserTeamFilter instance
     *
     * @param string $label
     * @param string $userRelationship
     */
    public function __construct(string $label, string $userRelationship = 'user')
    {
        parent::__construct('team', $label);

        $this->valueKey('id')
            ->labelKey('name')
            ->options(fn () => $this->teams())
            ->query(function ($builder, $value, $condition, $sqlOperator) use ($userRelationship) {
                return $builder->whereHas($userRelationship . '.teams', fn (Builder $query) => $query->where(
                    'teams.id',
                    $sqlOperator['operator'],
                    $value,
                    $condition
                ));
            });
    }

    /**
     * Get the filter teams
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function teams()
    {
        return resolve(TeamRepository::class)
            ->pushCriteria(OwnTeamsCriteria::class)
            ->get(['id', 'name'])->all();
    }
}
