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

namespace App\Support\DealBoard;

use Illuminate\Http\Request;
use App\Innoclapps\Facades\Innoclapps;
use App\Contracts\Repositories\DealRepository;
use App\Contracts\Repositories\StageRepository;
use App\Innoclapps\Criteria\FilterRulesCriteria;
use App\Innoclapps\Resources\Http\ResourceRequest;

class Board
{
    const RESOURCE_NAME = 'deals';

    const FILTERS_VIEW = 'deals-board';

    /**
     * @var \App\Contracts\Repositories\StageRepository
     */
    protected StageRepository $stageRepository;

    /**
     * Initialize new Board instance.
     *
     * @param \App\Contracts\Repositories\DealRepository $repository
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(protected DealRepository $repository, protected Request $request)
    {
        $this->stageRepository = resolve(StageRepository::class);
    }

    /**
     * Provides the board data
     *
     * @param int $pipelineId
     *
     * @return \Illuminate\Support\Collection
     */
    public function data(int $pipelineId)
    {
        $stages  = $this->stageRepository->getByPipeline($pipelineId);
        $summary = $this->summary($pipelineId);

        // Map the deals into the belonging stages
        return $this->mapStagesDeals($stages->map(function ($stage) use ($summary) {
            $stage->summary = $summary[$stage->id];

            return $stage;
        }), $this->getDeals($stages));
    }

    /**
     * Updates the board
     *
     * @param array $data
     *
     * @return void
     */
    public function update(array $data) : void
    {
        tap(new BoardUpdater(
            $data,
            $this->repository,
            $this->request->user()
        ), fn ($updater) => $updater->perform());
    }

    /**
     * Find all deals that belongs to the pipeline stages
     *
     * @param \Illuminate\Database\Eloquent\Collection $stages
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getDeals($stages)
    {
        return $this->repository->columns($this->getColumnsForSelect())
            ->pushCriteria($this->createFiltersCriteria())
            ->withCount(['incompleteActivitiesForUser as incomplete_activities_for_user_count'])
            ->scopeQuery(fn ($query) => $query->whereIn('stage_id', $stages->modelKeys()))->all();
    }

    /**
     * Optimize query by selecting fewer columns
     *
     * @return array
     */
    protected function getColumnsForSelect() : array
    {
        return ['id', 'stage_id', 'swatch_color', 'user_id', 'name', 'expected_close_date', 'amount', 'status'];
    }

    /**
     * Map the stages deals into appropriate stages
     *
     * @param \Illuminate\Support\Collection $stages
     * @param \Illuminate\Support\Collection $deals
     *
     * @return \Illuminate\Support\Collection
     */
    protected function mapStagesDeals($stages, $deals)
    {
        return $stages->map(function ($stage) use ($deals) {
            $stage->deals = $deals->where('stage_id', $stage->id);

            return $stage;
        })->sortBy('display_order');
    }

    /**
     * Get the summary for the board
     *
     * @param int $pipelineId
     *
     * @return \Illuminate\Support\Collection
     */
    public function summary(int $pipelineId)
    {
        return $this->stageRepository->columns('id')
            ->withSum(['deals' => fn ($query) => $this->applySummaryFilters($query)], 'amount')
            ->withCount(['deals' => fn ($query) => $this->applySummaryFilters($query)])
            ->getByPipeline($pipelineId)->mapWithKeys(fn ($stage) => [$stage->id => [
                'count' => $stage->deals_count,
                'value' => $stage->deals_sum_amount,
            ]]);
    }

    /**
     * Apply the summary filters to the given query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySummaryFilters($query)
    {
        return $this->createFiltersCriteria()->apply($query, $this->stageRepository);
    }

    /**
     * Create the criteria instance for the filters
     *
     * @return \App\Innoclapps\Contracts\Repository\CriteriaInterface
     */
    protected function createFiltersCriteria()
    {
        $resource = Innoclapps::resourceByName(static::RESOURCE_NAME);
        $rules    = $this->request->get('rules');
        $criteria = new FilterRulesCriteria(
            $rules,
            $resource->filtersForResource(app(ResourceRequest::class)->setResource($resource->name())),
            $this->request
        );

        return $criteria->setIdentifier($resource->name())->setView(static::FILTERS_VIEW);
    }
}
