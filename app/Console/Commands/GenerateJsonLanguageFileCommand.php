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
use App\Innoclapps\Translation\Translation;

class GenerateJsonLanguageFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bartu:json-language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate application json language file.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Translation::generateJsonLanguageFile();

        $this->info('Language file generated successfully.');
    }
}
