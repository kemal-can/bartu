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

namespace App\Innoclapps\Rules;

class UniqueResourceRule extends UniqueRule
{
    /**
     * Indicates whether to exclude the unique validation from import
     *
     * @var boolean
     */
    public $skipOnImport = false;

    /**
     * Create a new rule instance.
     *
     * @param string $modelName
     * @param string|int|null $ignore
     * @param string $column
     *
     * @return void
     */
    public function __construct($modelName, $ignore = 'resourceId', $column = 'NULL')
    {
        parent::__construct($modelName, $ignore, $column);
    }

    /**
     * Set whether the exclude this validation rule from import
     *
     * @param boolean $value
     *
     * @return static
     */
    public function skipOnImport($value)
    {
        $this->skipOnImport = $value;

        return $this;
    }
}
