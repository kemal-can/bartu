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

use App\Models\Stage;
use Illuminate\Http\Request;
use App\Http\Resources\StageResource;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Rules\UniqueResourceRule;
use App\Contracts\Repositories\StageRepository;
use App\Innoclapps\Contracts\Resources\Resourceful;

class PipelineStage extends Resource implements Resourceful
{
    /**
     * The column the records should be default ordered by when retrieving
     *
     * @var string
     */
    public static string $orderBy = 'name';

    /**
     * Get the underlying resource repository
     *
     * @return \App\Innoclapps\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(StageRepository::class);
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string
      */
    public function jsonResource() : string
    {
        return StageResource::class;
    }

    /**
     * Get the resource rules available for create
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'pipeline_id' => ['required', 'numeric'],
            'name'        => array_filter([
                'required',
                'max:191',
                'string',
                // Validate after the pipeline_id is provided
                $request->pipeline_id ? UniqueResourceRule::make(
                    Stage::class,
                    'resourceId'
                )->where('pipeline_id', $request->pipeline_id) : null,
            ]),
            'win_probability' => 'required|integer|max:100|min:0',
            'display_order'   => 'sometimes|integer',
        ];
    }
}
