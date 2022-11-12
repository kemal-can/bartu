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

namespace App\Resources\Actions;

use Illuminate\Support\Collection;
use App\Http\Requests\ActionRequest;
use App\Innoclapps\Actions\ActionFields;
use App\Innoclapps\Actions\DestroyableAction;
use App\Innoclapps\Repository\BaseRepository;

class DeleteAction extends DestroyableAction
{
    /**
     * Indicates that the action will be hidden on the index view
     *
     * @var boolean
     */
    public bool $hideOnIndex = true;

    /**
     * Indicates whether the action bulk deletes models
     *
     * @var boolean
     */
    public bool $isBulk = false;

    /**
     * Repository
     *
     * @var string
     */
    protected $repository;

    /**
     * Authorized to run callback
     *
     * @var callable
     */
    protected $authorizedToRunWhen;

    /**
     * Action name
     *
     * @var string
     */
    protected $name;

    /**
     * Repository action
     *
     * @var string
     */
    protected $deleteAction = 'delete';

    /**
     * Get the models repository
     *
     * @return string
     */
    public function repository() : string
    {
        if ($this->repository instanceof BaseRepository) {
            return $this->repository::class;
        }

        return $this->repository;
    }

    /**
     * Handle method
     *
     * @param \Illuminate\Support\Collection $models
     * @param \App\Innoclapps\Actions\ActionFields $fields
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        $repository = resolve($this->repository());
        $repository->{$this->deleteAction}($models);
    }

    /**
     * Add authorization callback for the action
     *
     * @param callable $callable
     *
     * @return static
     */
    public function authorizedToRunWhen(callable $callable) : static
    {
        $this->authorizedToRunWhen = $callable;

        return $this;
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param \App\Http\Requests\ActionRequest $request
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return boolean
     */
    public function authorizedToRun(ActionRequest $request, $model)
    {
        if (! $this->authorizedToRunWhen) {
            return $request->user()->can('delete', $model);
        }

        return call_user_func_array($this->authorizedToRunWhen, [$request, $model]);
    }

    /**
     * Use the given repository
     *
     * @param \App\Innoclapps\Repository\BaseRepository|string $repository
     *
     * @return static
     */
    public function useRepository($repository) : static
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Use the given name
     *
     * @param string $name
     *
     * @return static
     */
    public function useName(string $name) : static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set that the action will force delete the model
     *
     * @return static
     */
    public function forceDelete() : static
    {
        $this->deleteAction = 'forceDelete';

        return $this;
    }

    /**
     * Set that the action is bulk action
     *
     * @return static
     */
    public function isBulk() : static
    {
        $this->onlyOnIndex();
        $this->isBulk = true;

        return $this;
    }

    /**
     * Action name
     *
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * Get the URI key for the card.
     *
     * @return string
     */
    public function uriKey() : string
    {
        $key = $this->deleteAction === 'forceDelete' ? 'force-delete' : 'delete';

        return ($this->isBulk ? 'bulk-' : '') . $key;
    }
}
