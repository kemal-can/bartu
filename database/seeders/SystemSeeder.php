<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SettingsSeeder::class,
            MailableTemplatesSeeder::class,
            CountriesSeeder::class,
            CallOutcomeSeeder::class,
            PipelineSeeder::class,
            ActivityTypeSeeder::class,
            SourceSeeder::class,
            IndustrySeeder::class,
            FiltersSeeder::class,
        ]);
    }
}
