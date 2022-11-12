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

namespace App\Innoclapps\Fields;

use App\Innoclapps\Table\MorphToManyColumn;

class MorphToMany extends HasMany
{
    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\MorphToManyColumn
     */
    public function indexColumn() : MorphToManyColumn
    {
        return tap(new MorphToManyColumn(
            $this->hasManyRelationship,
            $this->labelKey,
            $this->label
        ), function ($column) {
            if ($this->counts()) {
                $column->count()->centered()->sortable();
            }
        });
    }
}
