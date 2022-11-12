<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Innoclapps\Facades\Innoclapps;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('bartu:clear-cache');
        Innoclapps::disableNotifications();

        $this->call([
            SystemSeeder::class,
            DemoDataSeeder::class,
        ]);

        settings([
            '_installed_date' => date('Y-m-d H:i:s'),
        ]);
    }
}
