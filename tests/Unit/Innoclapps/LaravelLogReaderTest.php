<?php

namespace Tests\Unit\Innoclapps;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use App\Innoclapps\LaravelLogReader;

class LaravelLogReaderTest extends TestCase
{
    protected function tearDown() : void
    {
        LaravelLogReader::glob(null);
        parent::tearDown();
    }

    public function test_it_can_read_log_files()
    {
        $reader = new LaravelLogReader(['date' => $date = date('Y-m-d')]);
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertEquals($date, $logs['date']);
        $this->assertArrayHasKey('filename', $logs);
        $this->assertArrayHasKey('log_dates', $logs);
        $this->assertArrayHasKey('logs', $logs);
        $this->assertNotNull(collect($logs['logs'])->where('message', 'Test log')->first(), 'The "Test log" was not found in the logs.');
    }

    public function test_it_uses_the_first_log_date_if_no_date_provided()
    {
        $reader = new LaravelLogReader();
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertEquals(date('Y-m-d'), $logs['date']);
    }

    public function test_it_can_determine_when_there_are_no_logs_available()
    {
        LaravelLogReader::glob(storage_path('logs/fake/laravel-*.log'));
        $reader = new LaravelLogReader();

        $logs = $reader->get();

        $this->assertFalse($logs['success']);
        $this->assertSame('No logs available', $logs['message']);
        $this->assertCount(0, $logs['log_dates']);
    }

    public function test_it_can_determine_when_there_are_no_logs_available_for_the_given_date()
    {
        $reader = new LaravelLogReader(['date' => $date = date('Y-m-d', strtotime('+1 year'))]);
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertFalse($logs['success']);
        $this->assertSame('No log file found with selected date ' . $date, $logs['message']);
    }
}
