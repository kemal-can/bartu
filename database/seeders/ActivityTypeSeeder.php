<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * @var array
     */
    public $types = [
        'Call'     => ['#a3e635', 'Phone'],
        'Meeting'  => ['#64748b', 'Users'],
        'Task'     => ['#ffd600', 'CheckCircle'],
        'Email'    => ['#818cf8', 'Mail'],
        'Deadline' => ['#f43f5e', 'Clock'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->types as $name => $options) {
            $model = \App\Models\ActivityType::create([
                'name'         => $name,
                'swatch_color' => $options[0],
                'icon'         => $options[1],
                'flag'         => strtolower($name),
            ]);

            if ($model->flag === 'task') {
                $model::setDefault($model->getKey());
            }
        }
    }
}
