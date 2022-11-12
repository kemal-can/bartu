<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Innoclapps\Facades\Permissions;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permissions::createMissing();
    }
}
