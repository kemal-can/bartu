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

namespace App\Criteria;

use Illuminate\Http\Request;
use App\Innoclapps\Date\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Innoclapps\ProvidesBetweenArgumentsViaString;
use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class ExportRequestCriteria implements CriteriaInterface
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * Create new ExportRequestCriteria instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Apply criteria in query repository
     *
     * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     * @param \App\Innoclapps\Contracts\Repository\RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        // If no ->with(['relation']) is called on the relation it will be model
        // instead will be Builder, because we are eager loading the fields relations
        // Will be Builder
        $createdAtColumn = ($model instanceof Builder ? $model->getModel() : $model)->getCreatedAtColumn();

        if ($period = $this->request->input('period')) {
            $betweenArgument = is_array($period) ?
                array_map(fn ($date) => Carbon::fromCurrentToAppTimezone($date), $period) :
                $this->getBetweenArguments($period);

            $model = $model->whereBetween($createdAtColumn, $betweenArgument);
        }

        $model->orderByDesc($createdAtColumn);

        return $model;
    }
}
