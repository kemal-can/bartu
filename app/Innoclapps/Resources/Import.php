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

namespace App\Innoclapps\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Innoclapps\Import\ImportViaFields;
use App\Innoclapps\Resources\Http\ImportRequest;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Import\Exceptions\ValidationException;
use App\Innoclapps\Contracts\Repositories\ImportRepository;

class Import extends ImportViaFields
{
    /**
     * @var integer
     */
    protected int $imported = 0;

    /**
     * @var integer
     */
    protected int $skipped = 0;

    /**
     * @var integer
     */
    protected int $duplicates = 0;

    /**
     * @var \App\Innoclapps\Models\Import
     */
    protected $import;

    /**
     * Duplicates validator callback
     *
     * @var callable|null
     */
    protected $validateDuplicatesUsing;

    /**
     * Perform callback on after save
     *
     * @var callable|null
     */
    protected $afterSaveCalback;

    /**
     * The current request that is in the loop for importing
     *
     * @var \App\Innoclapps\Resources\Http\ImportRequest|null
     */
    public static $currentRequest;

    /**
     * Create new Import instance
     *
     * @param \App\Innoclapps\Resources\Resource $resource
     */
    public function __construct(protected Resource $resource)
    {
    }

    /**
     * Try to perform the import
     *
     * @throws \App\Innoclapps\Import\Exceptions\ValidationException
     * @throws \App\Innoclapps\Import\Exceptions\ImportException
     *
     * @param \App\Innoclapps\Models\Import $import
     * @param string $disk
     *
     * @return void
     */
    public function perform($import, string $disk = 'local')
    {
        $repository = app(ImportRepository::class);

        $this->import = $import;

        try {
            $repository->update(['status' => 'in-progress'], $import->getKey());

            parent::perform($import->file_path, $import::disk());

            $repository->update([
                'status'     => 'finished',
                'imported'   => $this->imported,
                'skipped'    => $this->skipped,
                'duplicates' => $this->duplicates,
            ], $import->getKey());
        } catch (\Exception $e) {
            $repository->update(['status' => 'mapping'], $import->getKey());

            throw $e;
        }
    }

    /**
     * Initiate new import from the given file and start mapping the fields
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param \App\Models\User $user
     *
     * @return array
     */
    public function upload(UploadedFile $file, $user)
    {
        $repository = app(ImportRepository::class);
        $path       = $this->storeImportedFile($file, $repository);

        return $repository->create([
            'file_path'     => $path,
            'resource_name' => $this->resource->name(),
            'user_id'       => $user->getKey(),
            'status'        => 'mapping',
            'imported'      => 0,
            'duplicates'    => 0,
            'skipped'       => 0,
            'data'          => [
                'mappings' => (new ImportHeadingsMapper(
                    $path,
                    $this->resolveFields(),
                    $repository->model()::disk()
                ))->map(),
            ],
        ]);
    }

    /**
     * Store the imported file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param \App\Innoclapps\Repository\AppRepository $repository
     *
     * @return string|false
     */
    protected function storeImportedFile(UploadedFile $file, $repository)
    {
        $hashedFolderName = Str::random(15);

        return $file->storeAs(
            'imports' . DIRECTORY_SEPARATOR . $hashedFolderName,
            $file->getClientOriginalName(),
            $repository->model()::disk()
        );
    }

    /**
     * Add callback for duplicates validation
     *
     * @param callable $callback
     *
     * @return static
     */
    public function validateDuplicatesUsing(callable $callback) : static
    {
        $this->validateDuplicatesUsing = $callback;

        return $this;
    }

    /**
     * Add callback for after save
     *
     * @param callable $callback
     *
     * @return static
     */
    public function afterSave(callable $callback) : static
    {
        $this->afterSaveCalback = $callback;

        return $this;
    }

    /**
     * Provides the resource fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function fields()
    {
        return $this->resource->resolveFields();
    }

    /**
     * Handle the import
     *
     * @param array $rows
     *
     * @return void
     */
    public function array(array $rows)
    {
        $requests = $this->prepareRequestsForValidation($rows);

        $this->validate($requests);

        foreach ($requests as $request) {
            static::$currentRequest = $request;
            $this->save($request);
            static::$currentRequest = null;
        }
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row) : array
    {
        $mappings = $this->import->data['mappings'];

        return collect($mappings)->reject(function ($column) {
            return $column['skip'] || ! $column['attribute'];
        })->reduce(function ($carry, $column) use ($row, $mappings) {
            $carry[$column['attribute']] = $row[$column['original']];

            return $carry;
        }, []);
    }

    /**
     * Handle the model save for the given request
     *
     * @param \App\Innoclapps\Resources\Http\ImportRequest&\App\Innoclapps\Resources\Http\ResourceRequest
     *
     * @return void
     */
    protected function save(ImportRequest & ResourceRequest $request) : void
    {
        $request = $this->finalizeRequestData($request);

        if ($record = $this->searchForDuplicateRecord($request)) {
            if ($record->usesSoftDeletes() && $record->trashed()) {
                $request->resource()->repository()->restore($record->getKey());
            }

            $record = $this->updateRecord($record, $request);
        } else {
            $record = $this->createRecord($request);
        }

        if ($this->afterSaveCalback) {
            call_user_func_array($this->afterSaveCalback, [$record, $request]);
        }
    }

    /**
     * Create record
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \App\Innoclapps\Models\Model
     */
    protected function createRecord(ResourceRequest $request)
    {
        return tap($request->resource()
            ->setModel(null)
            ->resourcefulHandler($request)
            ->store(), function ($record) {
                $this->imported++;
            });
    }

    /**
     * Update record
     *
     * @param \App\Innoclapps\Models\Model $record
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \App\Innoclapps\Models\Model
     */
    protected function updateRecord($record, $request)
    {
        return tap($request->resource()
            ->setModel($record)
            ->resourcefulHandler($request)
            ->update($record->getKey()), function ($record) {
                $this->duplicates++;
            });
    }

    /**
     * Validate the given requests
     *
     * @param \Illuminate\Support\LazyCollection $requests
     *
     * @return void
     */
    protected function validate($requests)
    {
        $validationErrors = collect([]);

        foreach ($requests as $request) {
            // Allow fields to use this request to manipulate or validate data
            // e.q. on custom callback function get retrieve the request data
            static::$currentRequest = $request;

            if ($request->getValidator()->fails()) {
                $validationErrors = $validationErrors->merge($request->getValidator()->errors()->all());
            }

            static::$currentRequest = null;
        }

        if ($validationErrors->isNotEmpty()) {
            $validationErrors = $validationErrors->unique()->map(function ($message) {
                return __('validation.import.invalid', ['message' => $message]);
            });

            throw new ValidationException('The given data was invalid.', $validationErrors);
        }
    }

    /**
     * Try to find duplicate record from the request
     *
     * @param \App\Innoclapps\Resources\Http\ImportRequest $request
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function searchForDuplicateRecord($request)
    {
        if (($this->validateDuplicatesUsing && $record = call_user_func($this->validateDuplicatesUsing, $request)) ||
                    (method_exists($this, 'isDuplicate') && $record = $this->isDuplicate($request))) {
            return $record;
        }
    }
}
