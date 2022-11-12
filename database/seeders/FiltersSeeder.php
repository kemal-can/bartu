<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Innoclapps\Filters\Date;
use App\Innoclapps\Models\Filter;
use App\Resources\User\Filters\User;
use App\Enums\DealStatus as StatusEnum;
use App\Resources\Deal\Filters\DealStatus;
use App\Resources\Activity\Filters\OpenActivities;
use App\Resources\Activity\Filters\OverdueActivities;
use App\Resources\Activity\Filters\DueTodayActivities;
use App\Resources\Activity\Filters\DueThisWeekActivities;

class FiltersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['Contact', 'Company', 'Deal', 'Activity'] as $resource) {
            $this->{'seed' . $resource . 'Filters'}();
        }
    }

    public function seedContactFilters()
    {
        $this->newModelInstance([
            'identifier' => 'contacts',
            'name'       => 'contact.filters.my',
            'flag'       => 'my-contacts',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
            ],
        ])->save();


        $this->newModelInstance([
            'identifier' => 'contacts',
            'name'       => 'contact.filters.my_recently_assigned',
            'flag'       => 'my-recently-assigned-contacts',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
                Date::make('owner_assigned_date')->setOperator('is')->setValue('this_month')->toArray(),
            ],
        ])->save();
    }

    public function seedCompanyFilters()
    {
        $this->newModelInstance([
            'identifier' => 'companies',
            'name'       => 'company.filters.my',
            'flag'       => 'my-companies',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'companies',
            'name'       => 'company.filters.my_recently_assigned',
            'flag'       => 'my-recently-assigned-companies',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
                Date::make('owner_assigned_date')->setOperator('is')->setValue('this_month')->toArray(),
            ],
        ])->save();
    }

    public function seedActivityFilters()
    {
        $this->newModelInstance([
            'identifier' => 'activities',
            'name'       => 'activity.filters.open',
            'flag'       => 'open-activities',
            'rules'      => [
                OpenActivities::make()->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'activities',
            'name'       => 'activity.filters.due_today',
            'flag'       => 'due-today-activities',
            'rules'      => [
                DueTodayActivities::make()->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'activities',
            'name'       => 'activity.filters.due_this_week',
            'flag'       => 'due-this-week-activities',
            'rules'      => [
                DueThisWeekActivities::make()->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'activities',
            'name'       => 'activity.overdue',
            'flag'       => 'overdue-activities',
            'rules'      => [
                OverdueActivities::make()->setOperator('equal')->setValue('yes')->toArray(),
            ],
        ])->save();
    }

    public function seedDealFilters()
    {
        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.my',
            'flag'       => 'my-deals',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.my_recently_assigned',
            'flag'       => 'my-recently-assigned-deals',
            'rules'      => [
                User::make()->setOperator('equal')->setValue('me')->toArray(),
                Date::make('owner_assigned_date')->setOperator('is')->setValue('this_month')->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.created_this_month',
            'flag'       => 'deals-created-this-month',
            'rules'      => [
                Date::make('created_at')->setOperator('is')->setValue('this_month')->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.won',
            'flag'       => 'won-deals',
            'rules'      => [
                DealStatus::make()->setOperator('equal')->setValue(StatusEnum::won->name)->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.lost',
            'flag'       => 'lost-deals',
            'rules'      => [
                DealStatus::make()->setOperator('equal')->setValue(StatusEnum::lost->name)->toArray(),
            ],
        ])->save();

        $this->newModelInstance([
            'identifier' => 'deals',
            'name'       => 'deal.filters.open',
            'flag'       => 'open-deals',
            'rules'      => [
                DealStatus::make()->setOperator('equal')->setValue(StatusEnum::open->name)->toArray(),
            ],
        ])->save();
    }

    /**
     * Create new filter modal instance
     *
     * @param array $attributes
     *
     * @return \App\Innoclaps\Models\Filter
     */
    protected function newModelInstance($attributes)
    {
        return new Filter(array_merge([
            'is_shared'   => true,
            'is_readonly' => true,
        ], $attributes));
    }
}
