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

namespace App\Resources\Source\Fields;

use App\Innoclapps\Fields\BelongsTo;
use App\Http\Resources\SourceResource;
use App\Innoclapps\Facades\Innoclapps;
use App\Contracts\Repositories\SourceRepository;

class Source extends BelongsTo
{
    /**
     * Create new instance of Source field
     *
     * @param string $label Custom label
     */
    public function __construct($label = null)
    {
        parent::__construct('source', SourceRepository::class, $label ?? __('source.source'));

        $this->setJsonResource(SourceResource::class)
            ->options(Innoclapps::resourceByName('sources'))
            ->acceptLabelAsValue();
    }
}
