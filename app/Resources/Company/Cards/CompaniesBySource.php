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

namespace App\Resources\Company\Cards;

use Illuminate\Http\Request;
use App\Innoclapps\Charts\Presentation;
use App\Criteria\Company\OwnCompaniesCriteria;
use App\Contracts\Repositories\SourceRepository;
use App\Contracts\Repositories\CompanyRepository;

class CompaniesBySource extends Presentation
{
    /**
     * The default renge/period selected
     *
     * @var integer
     */
    public string|int|null $defaultRange = 30;

    /**
      * @var \Illuminate\Database\Eloquent\Collection
      */
    protected $sources;

    /**
     * Calculates companies by source
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $repository = resolve(CompanyRepository::class)
            ->pushCriteria(OwnCompaniesCriteria::class);

        return $this->byDays('created_at')->count($request, $repository, 'source_id')
            ->label(function ($value) {
                return $this->sources()->find($value)->name ?? 'N\A';
            });
    }

    /**
     * Get the ranges available for the chart.
     *
     * @return array
     */
    public function ranges() : array
    {
        return [
            7   => __('dates.periods.7_days'),
            15  => __('dates.periods.15_days'),
            30  => __('dates.periods.30_days'),
            60  => __('dates.periods.60_days'),
            90  => __('dates.periods.90_days'),
            365 => __('dates.periods.365_days'),
        ];
    }

    /**
     * Get all available sources
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function sources()
    {
        if (! $this->sources) {
            $this->sources = resolve(SourceRepository::class)->columns(['id', 'name'])->all();
        }

        return $this->sources;
    }

    /**
     * The card name
     *
     * @return string
     */
    public function name() : string
    {
        return __('company.cards.by_source');
    }
}
