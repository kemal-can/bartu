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

namespace App\Resources\Deal\Cards;

use App\Innoclapps\Charts\Presentation;
use App\Criteria\UserOrderedModelCriteria;
use App\Contracts\Repositories\StageRepository;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Contracts\Repositories\PipelineRepository;

abstract class DealPresentationCard extends Presentation
{
    /**
    * @var \Illuminate\Database\Eloquent\Collection
    */
    protected $stages;

    /**
     * Add stages labels to the result
     *
     * @param \App\Innoclapps\Charts\ChartResult $result
     *
     * @return \App\Innoclapps\Charts\ChartResult
     */
    protected function withStageLabels($result)
    {
        return $result->label(function ($value) {
            return $this->stages()->find($value)->name;
        });
    }

    /**
     * Get the deals pipeline for the card
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function getPipeline($request)
    {
        $repository = app(PipelineRepository::class);

        return ! $request->has('pipeline_id') ?
                $repository->pushCriteria(VisiblePipelinesCriteria::class)
                    ->pushCriteria(UserOrderedModelCriteria::class)
                    ->first()->getKey() :
                (int) $request->pipeline_id;
    }

    /**
     * Get all available stages
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function stages()
    {
        if (! $this->stages) {
            $this->stages = resolve(StageRepository::class)->columns(['id', 'name'])->all();
        }

        return $this->stages;
    }

    /**
    * The element's component.
    *
    * @var string
    */
    public function component() : string
    {
        return 'deal-presentation-card';
    }
}
