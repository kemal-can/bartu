<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class OptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bartu:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application by applying cache.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('optimize');
    }
}
