<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;

class TeamTest extends TestCase
{
    public function test_team_has_users()
    {
        $team = Team::factory()->has(User::factory()->count(2))->create();

        $this->assertCount(2, $team->users);
    }
}
