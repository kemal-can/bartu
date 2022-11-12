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

class PrivacyPolicyPlaceholder extends UrlPlaceholder
{
    /**
     * The placeholder tag
     *
     * @var string
     */
    public string $tag = 'privacy_policy';

    /**
     * Format the placeholder
     *
     * @param string|null $contentType
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        return privacy_url();
    }

    /**
     * Boot the placeholder and set default values
     *
     * @return void
     */
    public function boot()
    {
        $this->description(__('app.privacy_policy'));
    }
}
