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

use App\Innoclapps\Contracts\Presentable;

class UrlPlaceholder extends MailPlaceholder
{
    /**
     * The placeholder tag
     *
     * @var string
     */
    public string $tag = 'url';

    /**
    * Format the placeholder
    *
    * @param string|null $contentType
    *
    * @return string
    */
    public function format(?string $contentType = null)
    {
        return url(
            $this->value instanceof Presentable ? $this->value->path : $this->value
        );
    }

    /**
     * Boot the placeholder and set default values
     *
     * @return void
     */
    public function boot()
    {
        $this->description('URL');
    }
}
