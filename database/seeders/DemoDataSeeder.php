<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\User;
use App\Models\Source;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Pipeline;
use App\Models\CallOutcome;
use Illuminate\Support\Str;
use App\Models\ActivityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Repositories\ActivityRepository;
use App\Innoclapps\Contracts\Repositories\CountryRepository;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        settings([
            'company_country_id' => $this->demoCountryId(),
        ]);

        $users = User::factory(5)->create(
            ['super_admin' => collect([0, 1])->random()]
        );

        $pipeline = Pipeline::first();

        $users->each(function ($user, $index) use ($pipeline) {
            // For activity log causer and created_by
            Auth::loginUsingId($user->id);

            Product::factory()->for($user, 'creator')->create([
                'name' => $this->productNames()[$index],
            ]);

            Company::factory(5)->for($user)->for($user, 'creator')
                ->hasPhones()
                ->has(
                    Contact::factory()->for($user)->for($user, 'creator')
                        ->hasPhones()
                        ->has(Deal::factory()->for($pipeline)->for($user)->for($user, 'creator'))
                        ->for(Source::inRandomOrder()->first())
                        ->count(collect([0, 1, 2])->random())
                )
                ->for(Source::inRandomOrder()->first())
                ->has(Deal::factory()->for($pipeline)->for($user)->for($user, 'creator'))
                ->create()
                ->each(function ($company) use ($user) {
                    $this->seedCommonRelations($company, $user);

                    $company->deals->each(fn ($deal) => $this->seedCommonRelations($deal, $user));

                    $company->contacts->each(function ($contact) use ($user) {
                        $this->seedCommonRelations($contact, $user);

                        $contact->deals()->get()->each(fn ($deal) => $this->seedCommonRelations($deal, $user));
                    });
                });
        });

        $this->markRandomDealsAsLostOrWon();
        $this->setFirstUserCommonLogin();
    }

    /**
     * Set the first user common login details
     *
     * @return void
     */
    protected function setFirstUserCommonLogin()
    {
        $userAdmin                 = User::find(1);
        $userAdmin->name           = 'Admin';
        $userAdmin->email          = 'admin@test.com';
        $userAdmin->password       = bcrypt('123123');
        $userAdmin->remember_token = Str::random(10);
        $userAdmin->timezone       = 'Europe/Berlin';
        $userAdmin->access_api     = true;
        $userAdmin->super_admin    = true;
        $userAdmin->save();
    }

    /**
     * Seed the resources common relations
     *
     * @return void
     */
    protected function seedCommonRelations($model, $user)
    {
        $model->changelog()->update(
            $this->changelogAttributes($user)
        );

        $model->notes()->save(\App\Models\Note::factory()->for($user)->make());

        $model->calls()->save(
            \App\Models\Call::factory()
                ->for(CallOutcome::inRandomOrder()->first(), 'outcome')
                ->for($user)
                ->make()
        );

        $activity = $model->activities()->save(
            \App\Models\Activity::factory()->for($user)
                ->for($user, 'creator')
                ->for(ActivityType::inRandomOrder()->first(), 'type')
                ->make(['note' => null])
        );

        //  Attempted to lazy load [guestable] on model [App\Models\Guest] but lazy loading is disabled.
        $activity->load('guests.guestable');

        $activityRepository = resolve(ActivityRepository::class);
        $activityRepository->addGuest($activity, $user);

        if ($model instanceof \App\Models\Contact) {
            $activityRepository->addGuest($activity, $model);
        } else {
            if ($contact = $model->contacts?->first()) {
                $activityRepository->addGuest($activity, $contact);
            }
        }

        return $this;
    }

    /**
     * Get the country id for the demo
     *
     * @return int
     */
    protected function demoCountryId()
    {
        return app(CountryRepository::class)->findWhere([
            'name' => 'United States',
        ])->first()->getKey();
    }

    /**
     * Activity overwrite
     */
    protected function changelogAttributes($user)
    {
        return [
            'causer_id'   => $user->id,
            'causer_type' => $user::class,
            'causer_name' => $user->name,
        ];
    }

    /**
     * Mark random deals as won and lost
     *
     * @return void
     */
    protected function markRandomDealsAsLostOrWon()
    {
        Deal::take(5)->latest()->inRandomOrder()->get()->each->markAsLost('Probable cause');
        Deal::take(5)->oldest()->inRandomOrder()->get()->each->markAsWon();
    }

    /**
     * Get the available dummy data product names
     *
     * @return array
     */
    protected function productNames()
    {
        return ['SEO Optimization', 'Web Design', 'Consultant Services', 'MacBook Pro', 'Marketing Services'];
    }
}
