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

namespace App\Resources\Deal\Fields;

use App\Http\Resources\DealResource;
use App\Innoclapps\Fields\BelongsTo;
use App\Contracts\Repositories\DealRepository;

class Deal extends BelongsTo
{
    /**
     * Create new instance of Deal field
     *
     * @param string $relationName The relation name, snake case format
     * @param string $label Custom label
     * @param string $foreignKey Custom foreign key
     */
    public function __construct($relationName = 'deal', $label = null, $foreignKey = null)
    {
        parent::__construct($relationName, DealRepository::class, $label ?? __('deal.deal'), $foreignKey);

        $this->setJsonResource(DealResource::class)
            ->async('/deals/search');
    }
}
