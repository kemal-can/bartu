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
use Illuminate\Filesystem\Filesystem;

class ClearPurifierCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bartu:purifier-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the HTML Purifier cache.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->files->cleanDirectory(
            $this->laravel['config']->get('purifier.cachePath')
        );

        $this->info('HTML Purifier cache has been flushed!');
    }
}
