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

namespace App\Resources\Contact\Fields;

use App\Innoclapps\Fields\BelongsTo;
use App\Http\Resources\ContactResource;
use App\Contracts\Repositories\ContactRepository;

class Contact extends BelongsTo
{
    /**
     * Create new instance of Contact field
     *
     * @param string $relationName The relation name, snake case format
     * @param string $label Custom label
     * @param string $foreignKey Custom foreign key
     */
    public function __construct($relationName = 'contact', $label = null, $foreignKey = null)
    {
        parent::__construct($relationName, ContactRepository::class, $label ?? __('contact.contact'), $foreignKey);

        $this->labelKey('display_name')
            ->setJsonResource(ContactResource::class)
            ->async('/contacts/search')
            ->provideSampleValueUsing(fn () => [1, 2]);
    }
}
