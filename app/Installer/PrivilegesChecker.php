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

namespace App\Installer;

use Illuminate\Database\Connection;

class PrivilegesChecker
{
    /**
     * Initialize PrivilegesChecker instance
     *
     * @param \Illuminate\Database\Connection $connection
     */
    public function __construct(protected Connection $connection)
    {
    }

    /**
     * Check the privileges
     *
     * @throws \App\Installer\PrivilegeNotGrantedException
     *
     * @return void
     */
    public function check()
    {
        $testMethods = $this->getTesterMethods();
        $tester      = new DatabaseTest($this->connection);

        foreach ($testMethods as $test) {
            $tester->{$test}();

            throw_if($tester->getLastError(), new PrivilegeNotGrantedException($tester->getLastError()));
        }
    }

    /**
     * Get the tester methods
     *
     * @return array
     */
    protected function getTesterMethods()
    {
        return [
            // should be first as it's the most important for this test as all other tests are dropping the table
            'testDropTable',
            'testCreateTable',
            'testSelect',
            'testInsert',
            'testUpdate',
            'testDelete',
            'testAlter',
            'testIndex',
            'testReferences',
        ];
    }
}
