<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
    * @var array
    */
    public $sources = [
        'Organic search',
        'Paid search',
        'Email marketing',
        'Social media',
        'Referrals',
        'Other campaigns',
        'Direct traffic',
        'Offline Source',
        'Paid social',
        'Web Form',
    ];

    /**
    * Run the database seeds.
    *
    * @return void
    */
    public function run()
    {
        foreach ($this->sources as $source) {
            Source::create([
                'name' => $source,
                'flag' => $source === 'Web Form' ? 'web-form' : null,
            ]);
        }
    }
}
