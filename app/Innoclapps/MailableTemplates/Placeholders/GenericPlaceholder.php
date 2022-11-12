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

namespace App\Innoclapps\MailableTemplates\Placeholders;

use Exception;
use App\Innoclapps\Contracts\Presentable;

class GenericPlaceholder extends MailPlaceholder
{
    /**
     * Format the placeholder
     *
     * @param string|null $contentType
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        if ($this->value instanceof Presentable) {
            return $this->value->display_name;
        }

        return $this->value;
    }

    /**
     * Serialize the placeholder for the front end
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        $data = parent::jsonSerialize();

        if (! $data['tag']) {
            throw new Exception('"tag" not provided for generic placeholder.');
        }

        return $data;
    }
}
