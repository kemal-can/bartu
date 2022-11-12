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

use App\Innoclapps\Facades\Format;

class DatePlaceholder extends MailPlaceholder
{
    /**
     * The placeholder tag
     *
     * @var string
     */
    public string $tag = 'date';

    /**
     * The user the date is intended for
     *
     * @var null|\App\Models\User
     */
    protected $user;

    /**
     * Custom formatter callback
     *
     * @var null|callable
     */
    protected $formatCallback;

    /**
     * Format the placeholder
     *
     * @param string|null $contentType
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        if (is_callable($this->formatCallback)) {
            return call_user_func_array($this->formatCallback, [$this->value, $this->user]);
        }

        return Format::date($this->value, $this->user);
    }

    /**
     * Add custom format callback
     *
     * @param callable $callback
     *
     * @return static
     */
    public function formatUsing(callable $callback)
    {
        $this->formatCallback = $callback;

        return $this;
    }

    /**
     * The user the date is intended for
     *
     * @param \App\Models\User $user
     *
     * @return static
     */
    public function forUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
