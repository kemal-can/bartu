<?php

namespace Tests\Unit\Innoclapps\Macros\Str;

use Tests\TestCase;
use Illuminate\Support\Str;

class ClickableUrlsTest extends TestCase
{
    public function test_it_makes_urls_clickable()
    {
        $formatted = Str::clickable('Test https://bartucrm.com Test');

        $this->assertStringContainsString('<a href="https://bartucrm.com" rel="nofollow" target=\'_blank\'>https://bartucrm.com</a>', $formatted);
    }

    public function test_it_makes_multiple_urls_clickable()
    {
        $formatted = Str::clickable('Test https://bartucrm.com Test http://bartucrm.com');

        $this->assertStringContainsString('<a href="https://bartucrm.com" rel="nofollow" target=\'_blank\'>https://bartucrm.com</a>', $formatted);
        $this->assertStringContainsString('<a href="http://bartucrm.com" rel="nofollow" target=\'_blank\'>http://bartucrm.com</a>', $formatted);
    }

    public function test_it_makes_ftp_clickable()
    {
        $formatted = Str::clickable('Test ftp://127.0.01 Test');

        $this->assertStringContainsString('<a href="ftp://127.0.01" rel="nofollow" target=\'_blank\'>ftp://127.0.01</a>', $formatted);
    }

    public function test_it_makes_email_clickable()
    {
        $formatted = Str::clickable('Test email@exampe.com Test');

        $this->assertStringContainsString('<a href="mailto:email@exampe.com">email@exampe.com</a>', $formatted);
    }
}
