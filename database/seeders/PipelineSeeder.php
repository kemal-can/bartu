<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    /**
     * @var array
     */
    public $stages = [
        [
            'name'            => 'Qualified To Buy',
            'win_probability' => 20,
            'display_order'   => 1,
        ],
        [
            'name'            => 'Contact Made',
            'win_probability' => 40,
            'display_order'   => 2,
        ],
        [
            'name'            => 'Presentation Scheduled',
            'win_probability' => 60,
            'display_order'   => 3,
        ],
        [
            'name'            => 'Proposal Made',
            'win_probability' => 80,
            'display_order'   => 4,
        ],
        [
            'name'            => 'Appointment Scheduled',
            'win_probability' => 100,
            'display_order'   => 5,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pipeline = \App\Models\Pipeline::create([
            'name' => 'Sales Pipeline',
            'flag' => 'default',
        ]);

        $pipeline->stages()->createMany($this->stages);
    }
}
