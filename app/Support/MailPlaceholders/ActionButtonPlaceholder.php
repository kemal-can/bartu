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

namespace App\Support\MailPlaceholders;

use App\Innoclapps\MailableTemplates\Placeholders\UrlPlaceholder;

class ActionButtonPlaceholder extends UrlPlaceholder
{
    /**
     * Indicates the starting interpolation
     *
     * @var string
     */
    public string $interpolationStart = '{{{';

    /**
     * Indicates the ending interpolation
     *
     * @var string
     */
    public string $interpolationEnd = '}}}';

    /**
     * The placeholder tag
     *
     * @var string
     */
    public string $tag = 'action_button';

    /**
     * Format the placeholder
     *
     * @param string|null $contentType
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        if ($contentType === 'text') {
            return parent::format();
        }

        return view('mail.button', [
            'url'  => parent::format(),
            'text' => __('mail_template.placeholders.view_record'),
        ])->render();
    }

    /**
     * Boot the placeholder and set default values
     *
     * @return void
     */
    public function boot()
    {
        $this->description('Formatted action button.');
    }
}
