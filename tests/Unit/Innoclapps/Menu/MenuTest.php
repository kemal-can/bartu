<?php

namespace Tests\Unit\Innoclapps\Menu;

use Tests\TestCase;
use App\Innoclapps\Facades\Menu;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Menu\Item as MenuItem;

class MenuTest extends TestCase
{
    public function test_menu_item_can_be_added()
    {
        $route = '/dummy-route';

        Innoclapps::booting(function () use ($route) {
            Menu::clear();
            Menu::register(
                MenuItem::make('Test', $route)
            );
        });

        Innoclapps::boot();

        $this->assertEquals($route, Menu::get()->first()->route);
    }

    public function test_user_cannot_see_menu_items_that_is_not_supposed_to_be_seen()
    {
        $this->asRegularUser()->signIn();

        Menu::register(MenuItem::make('test-item-1', '/')
            ->canSee(function () {
                return false;
            }));

        Menu::register(MenuItem::make('test-item-2', '/')
            ->canSeeWhen('dummy-ability'));

        Menu::register(MenuItem::make('test-item-3', '/'));

        $this->assertCount(1, Menu::get());
    }
}
