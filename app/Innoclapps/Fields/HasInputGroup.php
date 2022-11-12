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

trait HasInputGroup
{
    /**
     * A custom icon to be incorporated in input group
     *
     * @var null|string
     */
    public ?string $icon = null;

    /**
     * Input group append - right
     *
     * @param string $value
     *
     * @return static
     */
    public function inputGroupAppend($value) : static
    {
        if ($this->supportsInputGroup()) {
            $this->withMeta(['inputGroupAppend' => $value]);
        }

        return $this;
    }

    /**
     * Input group prepend - left
     *
     * @param string $value
     *
     * @return static
     */
    public function inputGroupPrepend($value) : static
    {
        if ($this->supportsInputGroup()) {
            $this->withMeta(['inputGroupPrepend' => $value]);
        }

        return $this;
    }

    /**
     * Checks whether the field support input group
     *
     * @return boolean
     */
    public function supportsInputGroup() : bool
    {
        return property_exists($this, 'supportsInputGroup') && (bool) $this->supportsInputGroup;
    }

    /**
     * Append icon to the field
     *
     * @param string $icon
     *
     * @return static
     */
    public function appendIcon(string $icon) : static
    {
        return $this->icon($icon, true);
    }

    /**
     * Prepend icon to the field
     *
     * @param string $icon
     *
     * @return static
     */
    public function prependIcon(string $icon) : static
    {
        return $this->icon($icon, false);
    }

    /**
     * Custom input group icon
     *
     * @param string $icon icon name
     * @param boolean $append whether to append or prepend the icon
     *
     * @return static
     */
    public function icon(string $icon, bool $append = true) : static
    {
        if ($this->supportsInputGroup()) {
            $this->icon = $icon;
            $method     = $append ? 'inputGroupAppend' : 'inputGroupPrepend';

            $this->{$method}(true);
        }

        return $this;
    }
}
