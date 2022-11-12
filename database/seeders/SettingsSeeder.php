<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        settings()->flush();

        $options = default_setting()->get();

        foreach ($options as $name => $value) {
            settings()->set([$name => $value]);
        }

        settings()->save();

        config(['mediable.allowed_extensions' => array_map(
            fn ($extension)                   => trim(str_replace('.', '', $extension)),
            explode(',', settings('allowed_extensions'))
        )]);
    }
}
