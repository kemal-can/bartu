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

namespace App\Resources\Deal;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\PipelineResource;
use App\Criteria\UserOrderedModelCriteria;
use App\Innoclapps\Rules\UniqueResourceRule;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Contracts\Repositories\PipelineRepository;
use App\Innoclapps\Contracts\Resources\Resourceful;

class Pipeline extends Resource implements Resourceful
{
    /**
     * Get the underlying resource repository
     *
     * @return \App\Innoclapps\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(PipelineRepository::class);
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string
      */
    public function jsonResource() : string
    {
        return PipelineResource::class;
    }

    /**
      * Set the criteria that should be used to fetch only own data for the user
      *
      * @return string
      */
    public function ownCriteria() : string
    {
        return VisiblePipelinesCriteria::class;
    }

    /**
     * Apply the order from the resource to the given repository
     *
     * @param \App\Innoclapps\Repositories\AppRepository $repository
     *
     * @return \App\Innoclapps\Repositories\AppRepository
     */
    public function applyOrder($repository)
    {
        return $repository->pushCriteria(UserOrderedModelCriteria::class);
    }

    /**
     * Prepare index query
     *
     * @param null|\App\Innoclapps\Repositories\AppRepository $repository
     *
     * @return \App\Innoclapps\Repositories\AppRepository
     */
    public function indexQuery($repository = null)
    {
        $repository = parent::indexQuery($repository);

        return $repository->with(['userOrder']);
    }

    /**
     * Get the resource rules available for create and update
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'stages'                   => 'sometimes|required|array',
            'stages.*.name'            => 'required|distinct|max:191|string',
            'stages.*.win_probability' => 'required|integer|max:100|min:0',
            'stages.*.display_order'   => 'sometimes|integer',

            'name' => [
                'required',
                'string',
                'max:191',
                UniqueResourceRule::make(static::model()),
            ],
        ];
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     *
     * @return array
     */
    public function validationMessages() : array
    {
        return [
            'stages.*.name.required' => __('validation.required', [
                'attribute' => Str::lower(__('deal.stage.name')),
            ]),
            'stages.*.name.distinct' => __('validation.distinct', [
                'attribute' => Str::lower(__('deal.stage.name')),
            ]),
            'stages.*.win_probability.required' => __('validation.required', [
                'attribute' => Str::lower(__('deal.stage.win_probability')),
            ]),
        ];
    }
}
