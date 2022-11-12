<?php

namespace Tests;

use Illuminate\Support\Str;
use App\Innoclapps\Application;
use Tests\Fixtures\EventResource;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Tests\Fixtures\CalendarResource;
use App\Innoclapps\Timeline\Timeline;
use App\Innoclapps\Workflow\Workflows;
use Spatie\Permission\PermissionRegistrar;
use App\Innoclapps\Facades\MailableTemplates;
use Database\Seeders\MailableTemplatesSeeder;
use App\Support\ChangeLoggers\LogsModelChanges;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Innoclapps\Fields\Manager as FieldsManager;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, CreatesUser, RefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    /**
     * @var \Illuminate\Support\Collection
     */
    protected static $models;

    /**
    * Define hooks to migrate the database before and after each test.
    *
    * @see \Illuminate\Foundation\Testing\LazilyRefreshDatabase
    *
    * @return void
    */
    public function refreshDatabase()
    {
        $database = $this->app->make('db');

        $database->beforeExecuting(function () {
            if (RefreshDatabaseState::$lazilyRefreshed) {
                return;
            }

            RefreshDatabaseState::$lazilyRefreshed = true;

            $this->baseRefreshDatabase();
            $this->seed(MailableTemplatesSeeder::class);
            $this->artisan('migrate', ['--path' => 'tests/Migrations']);
        });

        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$lazilyRefreshed = false;
        });
    }

    /**
     * Setup the tests
     *
     * @return void
     */
    protected function setUp() : void
    {
        Application::$resources = new Collection;

        Workflows::$triggers           = [];
        Workflows::$eventOnlyListeners = [];
        Workflows::$processed          = [];

        parent::setUp();

        $this->registerTestResources();
        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * Tear down the tests
     *
     * @return void
     */
    protected function tearDown() : void
    {
        $this->tearDownChangelog();
        Application::setImportStatus(false);
        Timeline::flushPinableSubjects();
        FieldsManager::flushCache();
        \Spatie\Once\Cache::getInstance()->flush();
        MailableTemplates::autoDiscovery(true);
        MailableTemplates::flushCache();
        app(CustomFieldRepository::class)->flushCache();

        parent::tearDown();
    }

    /**
     * Teardown changelog data
     *
     * @return void
     */
    protected function tearDownChangelog()
    {
        foreach (static::listModels() as $model) {
            if (in_array(LogsModelChanges::class, class_uses_recursive($model))) {
                $model::$afterSyncCustomFieldOptions[$model]  = [];
                $model::$beforeSyncCustomFieldOptions[$model] = [];

                $model::$changesPipes = [];
            }
        }
    }

    /**
     * Register the tests resources
     *
     * @return void
     */
    protected function registerTestResources()
    {
        Application::resources([
            EventResource::class,
            CalendarResource::class,
        ]);
    }

    /**
     * List the application available models
     *
     * @return \Illuminate\Support\Collection
     */
    protected function listModels()
    {
        if (! static::$models) {
            static::$models = collect((new Finder)->in([
                app_path('Models'),
                app_path('Innoclapps/Models'),
            ])->files()->name('*.php'))
                ->map(fn ($model) => app()->getNamespace() . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($model->getRealPath(), realpath(app_path()) . DIRECTORY_SEPARATOR)
                ));
        }

        return static::$models;
    }
}
