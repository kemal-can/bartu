<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CallOutcomeSeeder extends Seeder
{
    /**
     * @var array
     */
    public $outcomes = [
        'No Answer'                  => '#f43f5e',
        'Busy'                       => '#f43f5e',
        'Wrong Number'               => '#8898aa',
        'Unavailable'                => '#8898aa',
        'Left voice message'         => '#ffd600',
        'Moved conversation forward' => '#a3e635',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->outcomes as $name => $color) {
            \App\Models\CallOutcome::create(['name' => $name, 'swatch_color' => $color]);
        }
    }
}
