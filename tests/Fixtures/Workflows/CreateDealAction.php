<?php

namespace Tests\Fixtures\Workflows;

use App\Innoclapps\Fields\Date;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\Select;
use App\Innoclapps\Fields\Numeric;
use App\Innoclapps\Workflow\Action;
use App\Resources\Deal\Fields\Pipeline;
use App\Resources\Deal\Fields\PipelineStage;
use App\Contracts\Repositories\UserRepository;

class CreateDealAction extends Action
{
    /**
     * Run the trigger
     */
    public function run()
    {
    }

    /**
     * Action available fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
            Text::make('name', 'Name'),
            Pipeline::make('Pipeline'),
            PipelineStage::make('Stage'),
            Numeric::make('amount', 'Amount'),
            Date::make('expected_close_date', 'Expected Close Date')->clearable(),
            Select::make('user_id')->options(function () {
                return resolve(UserRepository::class)->all()->map(function ($user) {
                    return [
                        'value' => $user->id,
                        'label' => $user->name,
                    ];
                });
            }),
        ];
    }

    /**
    * Action name
    *
    * @return string
    */
    public static function name() : string
    {
        return 'Create new deal';
    }
}
