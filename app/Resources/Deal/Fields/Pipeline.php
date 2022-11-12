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

namespace App\Resources\Deal\Fields;

use App\Innoclapps\Fields\BelongsTo;
use App\Http\Resources\PipelineResource;
use App\Criteria\UserOrderedModelCriteria;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Contracts\Repositories\PipelineRepository;

class Pipeline extends BelongsTo
{
    /**
     * Pipelines repository
     *
     * @var \App\Contracts\Repositories\PipelineRepository
     */
    protected $repository;

    /**
     * Creat new Pipeline instance field
     *
     * @param string $label Custom label
     */
    public function __construct($label = null)
    {
        $this->repository = resolve(PipelineRepository::class);

        parent::__construct('pipeline', $this->repository, $label ?: __('fields.deals.pipeline.name'));

        $this->setJsonResource(PipelineResource::class)
            ->emitChangeEvent()
            ->withDefaultValue(function () {
                return $this->repository->withResponseRelations()
                    ->resetCriteria()
                    ->pushCriteria(VisiblePipelinesCriteria::class)
                    ->pushCriteria(UserOrderedModelCriteria::class)
                    ->first();
            })
            ->acceptLabelAsValue();
    }

    /**
     * Provides the BelongsTo instance options
     *
     * @return array
     */
    public function resolveOptions()
    {
        return $this->repository->with('stages')
            ->columns(['id', 'name'])
            ->resetCriteria()
            ->pushCriteria(VisiblePipelinesCriteria::class)
            ->pushCriteria(UserOrderedModelCriteria::class)
            ->all();
    }
}
