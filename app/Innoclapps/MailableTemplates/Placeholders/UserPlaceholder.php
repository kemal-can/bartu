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

use App\Innoclapps\Facades\Innoclapps;

class UserPlaceholder extends MailPlaceholder
{
    /**
     * The placeholder tag
     *
     * @var string
     */
    public string $tag = 'user';

    /**
     * @var string
     */
    public string $labelKey = 'name';

    /**
     * Format the placeholder
     *
     * @param string|null $contentType
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        return is_a($this->value, Innoclapps::getUserRepository()->model()) ?
            $this->value->{$this->labelKey} :
            $this->value;
    }

    /**
     * Set the user label key
     *
     * @param string $key
     *
     * @return static
     */
    public function labelKey(string $key) : static
    {
        $this->labelKey = $key;

        return $this;
    }

    /**
     * Boot the placeholder and set default values
     *
     * @return void
     */
    public function boot()
    {
        $this->description(__('user.user'));
    }
}
