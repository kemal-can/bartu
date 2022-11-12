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
use App\Http\Resources\StageResource;
use App\Contracts\Repositories\StageRepository;

class PipelineStage extends BelongsTo
{
    /**
     * Customer field component
     *
     * @var string
     */
    public $component = 'pipeline-stage-field';

    /**
     * Stages repository
     *
     * @var \App\Contracts\Repositories\StageRepository
     */
    protected $repository;

    /**
     * Indicates whether the field accept label as value
     *
     * @var boolean
     */
    public bool $acceptLabelAsValue = true;

    /**
     * Creat new PipelineStage instance field
     *
     * @param string|null $label
     */
    public function __construct($label = null)
    {
        parent::__construct('stage', StageRepository::class, $label ?: __('fields.deals.stage.name'));

        $this->setJsonResource(StageResource::class)
            ->creationRules('required')
            ->updateRules(['required_with:pipeline_id', 'filled'])
            ->required()
            ->acceptLabelAsValue();
    }

    /**
      * Provides the PipelineStage instance options
      *
      * We using dependable field, we need to provide all the options
      *
      * @return array
      */
    public function resolveOptions()
    {
        return $this->repository->orderBy('display_order')->all();
    }
}
