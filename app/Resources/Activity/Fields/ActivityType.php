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

namespace App\Resources\Activity\Fields;

use App\Innoclapps\Fields\BelongsTo;
use App\Models\ActivityType as Model;
use App\Http\Resources\ActivityTypeResource;
use App\Contracts\Repositories\ActivityTypeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityType extends BelongsTo
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'activity-type-field';

    /**
     * Create new instance of ActivityType field
     */
    public function __construct()
    {
        $repository = resolve(ActivityTypeRepository::class);

        parent::__construct('type', $repository, __('activity.type.type'));

        $this->withDefaultValue(function () use ($repository) {
            try {
                return $repository->columns(['id', 'name'])->find(Model::getDefaultType());
            } catch (ModelNotFoundException $e) {
            }
        })
            ->setJsonResource(ActivityTypeResource::class)
            ->tapIndexColumn(function ($column) {
                $column->label(__('activity.type.type'))
                    ->select(['icon', 'swatch_color'])
                    ->primary(false)
                    ->width('130px')
                    ->minWidth('130px');
            })
            ->acceptLabelAsValue(false);
    }
}
