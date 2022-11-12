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

namespace App\Innoclapps\Resources;

use JsonSerializable;
use Illuminate\Support\Collection;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Criteria\RequestCriteria;

class GlobalSearch implements JsonSerializable
{
    /**
     * Total results
     *
     * @var integer
     */
    protected int $take = 5;

    /**
     * Initialize global search for the given resources
     *
     * @param \Illuminate\Support\Collection $resources
     */
    public function __construct(protected Collection $resources)
    {
    }

    /**
     * Get the search result
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $result = new Collection([]);

        $this->resources->reject(fn ($resource) => ! $resource::searchable())
            ->each(function ($resource) use (&$result) {
                $result->push([
                    'title' => $resource->label(),
                    'data'  => $this->prepareSearchQuery($resource->repository(), $resource)
                        ->all()
                        ->whereInstanceOf(Presentable::class)
                        ->map(function ($model) use ($resource) {
                            return $this->data($model, $resource);
                        }),
                ]);
            });

        return $result->reject(fn ($result) => $result['data']->isEmpty())->values();
    }

    /**
     * Prepare the search query
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     * @param \App\Innoclapps\Resources\Resource $resource
     *
     * @return \App\Innoclapps\Repository\BaseRepository
     */
    protected function prepareSearchQuery($repository, $resource)
    {
        if ($ownCriteria = $resource->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        return $repository->pushCriteria(RequestCriteria::class)->limit($this->take);
    }

    /**
     * Provide the model data for the response
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \App\Innoclapps\Resources\Resource $resource
     *
     * @return array
     */
    protected function data($model, $resource) : array
    {
        return [
            'path'               => $model->path,
            'display_name'       => $model->display_name,
            'created_at'         => $model->created_at,
            $model->getKeyName() => $model->getKey(),
        ];
    }

    /**
     * Serialize GlobalSearch class
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->get()->all();
    }
}
