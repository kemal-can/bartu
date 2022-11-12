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

namespace App\Resources\Product;

use Illuminate\Http\Request;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Table\Table;
use App\Models\BillableProduct;
use App\Innoclapps\Fields\Boolean;
use App\Innoclapps\Fields\Numeric;
use App\Innoclapps\Fields\DateTime;
use App\Innoclapps\Fields\Textarea;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\ProductResource;
use App\Models\Product as ProductModel;
use App\Resources\Actions\DeleteAction;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Criteria\Product\OwnProductsCriteria;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Innoclapps\Criteria\WithTrashedCriteria;
use App\Contracts\Repositories\ProductRepository;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Contracts\Resources\Importable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Resources\Product\Cards\ProductPerformance;
use App\Innoclapps\Contracts\Resources\AcceptsCustomFields;

class Product extends Resource implements Resourceful, Tableable, Importable, Exportable, AcceptsCustomFields
{
    /**
    * The column the records should be default ordered by when retrieving
    *
    * @var string
    */
    public static string $orderBy = 'name';

    /**
    * Indicates whether the resource is globally searchable
    *
    * @var boolean
    */
    public static bool $globallySearchable = true;

    /**
    * Indicates whether the resource fields are customizeable
    *
    * @var boolean
    */
    public static bool $fieldsCustomizable = true;

    /**
    * Get the underlying resource repository
    *
    * @return \App\Innoclapps\Repository\AppRepository
    */
    public static function repository()
    {
        return resolve(ProductRepository::class);
    }

    /**
    * Provide the resource table class
    *
    * @param \App\Innoclapps\Repository\BaseRepository $repository
    * @param \Illuminate\Http\Request $request
    *
    * @return \App\Innoclapps\Table\Table
    */
    public function table($repository, Request $request) : Table
    {
        $table = new ProductTable($repository, $request);

        $table->customizeable = true;

        return $table;
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return ProductResource::class;
    }

    /**
    * Get the resource importable class
    *
    * @return \App\Innoclapps\Resources\Import
    */
    public function importable() : Import
    {
        return parent::importable()->validateDuplicatesUsing(function ($request) {
            return $this->lookUpForDuplicateImportProduct($request);
        });
    }

    /**
    * Set the available resource fields
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function fields(Request $request) : array
    {
        return [
            Text::make('name', __('product.name'))->rules([
                'required', 'string', 'max:191',
                ])->unique(static::model())
                ->primary()
                ->tapIndexColumn(fn ($column) => $column->width('320px')->minWidth('320px')),

                Text::make('sku', __('product.sku'))
                    ->unique(ProductModel::class)
                    ->validationMessages([
                    'unique' => __('product.validation.sku.unique'),
                ])->rules('nullable', 'string', 'max:191'),

                Textarea::make('description', __('product.description'))
                    ->rules('nullable', 'string')
                    ->strictlyForForms(),

                Numeric::make('unit_price', __('product.unit_price'))->rules('required')
                    ->currency()
                    ->primary()
                    ->colClass('col-span-12 sm:col-span-6')
                    ->tapIndexColumn(fn ($column) => $column->primary(false)),

                Numeric::make('direct_cost', __('product.direct_cost'))
                    ->colClass('col-span-12 sm:col-span-6')
                    ->tapIndexColumn(fn ($column) => $column->hidden())
                    ->currency(),

                Numeric::make('tax_rate', __('product.tax_rate'))
                    ->withDefaultValue(fn () => BillableProduct::defaultTaxRate())
                    ->tapIndexColumn(function ($column) {
                        $column->primary(false)->displayAs(
                            fn ($model) => empty($model->tax_rate) ? '--' : $model->tax_rate . '%'
                        );
                    })
                    ->inputGroupAppend('%')
                    ->withMeta(['attributes' => ['max' => 100]])
                    ->precision(3)
                    ->provideSampleValueUsing(fn () => array_rand(array_flip([10, 18, 20])))
                    ->colClass('col-span-12 sm:col-span-6')
                    ->saveUsing(function ($request, $requestAttribute, $value, $field) {
                        if ($request->has($requestAttribute)) {
                            $value = (is_int($value) || is_float($value) || (is_numeric($value) && ! empty($value))) ? $value : 0;
                        }

                        return [$field->attribute => $value];
                    })
                    ->primary(),

                Text::make('tax_label', __('product.tax_label'))
                    ->withDefaultValue(BillableProduct::defaultTaxLabel())
                    ->hideFromIndex()
                    ->primary()
                    ->colClass('col-span-12 sm:col-span-6')
                    ->tapIndexColumn(fn ($column) => $column->primary(false))
                    ->rules('nullable', 'string'),

                Text::make('unit', __('product.unit'))
                    ->provideSampleValueUsing(fn () => array_rand(array_flip(['kg', 'lot'])))
                    ->rules('nullable', 'string')
                    ->hideFromIndex(),

                Boolean::make('is_active', __('product.is_active'))->rules('nullable', 'boolean')
                    ->withDefaultValue(true)
                    ->primary()
                    ->excludeFromExport()
                    ->tapIndexColumn(fn ($column) => $column->primary(false)),

                User::make(__('app.created_by'), 'creator', 'created_by')
                    ->strictlyForIndex()
                    ->excludeFromImport()
                    ->hidden(),

                DateTime::make('updated_at', __('app.updated_at'))
                    ->excludeFromImportSample()
                    ->strictlyForIndex()
                    ->hidden(),

                DateTime::make('created_at', __('app.created_at'))
                    ->excludeFromImportSample()
                    ->strictlyForIndex()
                    ->hidden(),
            ];
    }

    /**
    * Get the menu items for the resource
    *
    * @return array
    */
    public function menu() : array
    {
        return [
            MenuItem::make(static::label(), '/products', 'MenuAlt1')
                ->position(40)
                ->inQuickCreate()
                ->keyboardShortcutChar('P'),
        ];
    }

    /**
     * Provides the resource available actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function actions(ResourceRequest $request) : array
    {
        return [
            new Actions\MarkAsActive,
            new Actions\MarkAsInactive,
            new Actions\UpdateUnitPrice,
            new Actions\UpdateTaxRate,

            (new DeleteAction)->isBulk()
                ->useName(__('app.delete'))
                ->useRepository(static::repository())
                ->authorizedToRunWhen(
                    fn ($request, $model) => $request->user()->can('bulk delete products')
                ),
        ];
    }

    /**
    * Get the resource available cards
    *
    * @return array
    */
    public function cards() : array
    {
        return [
            (new ProductPerformance())->onlyOnDashboard(),
        ];
    }

    /**
    * Try to find a duplicate product for the import request
    *
    * @param \App\Innoclapps\Resources\Http\ImportRequest $request
    *
    * @return \App\Models\Contact|null
    */
    protected function lookUpForDuplicateImportProduct($request)
    {
        $cursor = once(function () {
            return $this->repository()->pushCriteria(WithTrashedCriteria::class)->cursor();
        });

        return $cursor->filter(function ($product) use ($request) {
            return strcasecmp($product->name, $request->name) === 0 ||
                (! empty($product->sku) && strcasecmp($product->sku, $request->sku) === 0);
        })->first();
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string
    */
    public function ownCriteria() : string
    {
        return OwnProductsCriteria::class;
    }

    /**
    * Get the displayable singular label of the resource.
    *
    * @return string
    */
    public static function singularLabel() : string
    {
        return __('product.product');
    }

    /**
    * Get the displayable label of the resource.
    *
    * @return string
    */
    public static function label() : string
    {
        return __('product.products');
    }

    /**
    * Register permissions for the resource
    *
    * @return void
    */
    public function registerPermissions() : void
    {
        $this->registerCommonPermissions();
    }

    /**
    * Register the settings menu items for the resource
    *
    * @return array
    */
    public function settingsMenu() : array
    {
        return [
            SettingsMenuItem::make(__('company.companies'), '/settings/companies', 'OfficeBuilding')->order(24),
        ];
    }
}
