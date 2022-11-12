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

namespace App\Support\WebForm;

use App\Innoclapps\Facades\Format;
use App\Innoclapps\Models\Changelog;
use App\Innoclapps\Facades\Innoclapps;

class FormSubmission
{
    /**
     * @param \App\Innoclapps\Models\Changelog $changelog
     */
    public function __construct(protected Changelog $changelog)
    {
    }

    /**
     * Get the web form submission data
     *
     * @return array
     */
    public function data()
    {
        return $this->changelog->properties;
    }

    /**
     * Parse the displayable value
     *
     * @param string $value
     * @param array $property
     *
     * @return string
     */
    protected function parseValue($value, $property)
    {
        if (! empty($value)) {
            if (isset($property['dateTime'])) {
                $value = Format::dateTime($value);
            } elseif (isset($property['date'])) {
                $value = Format::date($value);
            }
        }

        return $value !== null ? $value : '/';
    }

    /**
     * Get the given resource label
     *
     * @param string $name
     *
     * @return string
     */
    protected function getResourceLabel($name)
    {
        return Innoclapps::resourceByName($name)->singularLabel();
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        $payload = '';

        foreach ($this->data() as $property) {
            $payload .= '<div>';
            $payload .= $this->getResourceLabel($property['resourceName']);
            $payload .= '  ' . '<span style="font-weight:bold;">' . $property['label'] . '</span>';
            $payload .= '</div>';
            $payload .= '<p style="margin-top:5px;">';
            $payload .= $this->parseValue($property['value'], $property);
            $payload .= '</p>';
        }

        return $payload;
    }
}
