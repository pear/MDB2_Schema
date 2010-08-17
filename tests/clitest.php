<?php /* vim: se et ts=4 sw=4 sts=4 fdm=marker: */
/**
 * Copyright (c) 1998-2010 Manuel Lemos, Tomas V.V.Cox,
 * Stig. S. Bakken, Lukas Smith, Igor Feghali
 * All rights reserved.
 *
 * MDB2_Schema enables users to maintain RDBMS independant schema files
 * in XML that can be used to manipulate both data and database schemas
 * This LICENSE is in the BSD license style.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,
 * Lukas Smith, Igor Feghali nor the names of his contributors may be
 * used to endorse or promote products derived from this software
 * without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE
 * REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 *  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
 * WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5
 *
 * @category Database
 * @package  MDB2_Schema
 * @author   Paul Cooper <pgc@ucecom.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  SVN: $Id$
 * @link     http://pear.php.net/packages/MDB2_Schema
 */

/*
 This is a small test suite for MDB2 using PHPUnit

 This is the command line version and should be used like so;

 php -q clitest.php

 This will run through all tests in all testcases (as defined in
 test_setup.php). To run individual tests add their names to the command
 line and all testcases will be searched for matching test names, e.g.

 php -q clitest.php teststorage testreplace
*/

require_once 'test_setup.php';
require_once 'PHPUnit.php';
require_once 'testUtils.php';
require_once 'MDB2.php';
require_once 'Console_TestListener.php';

MDB2::loadFile('Date');

foreach ($testcases as $testcase) {
    include_once $testcase.'.php';
}

$database = 'driver_test';

$inputMethods = $argv;

if ($argc > 1) {
    array_shift($inputMethods);
    $exclude = false;
    if ($inputMethods[0] == '-exclude') {
        array_shift($inputMethods);
        $exclude = true;
    }
    foreach ($testcases as $testcase) {
        $possibleMethods = getTests($testcase);
        if ($exclude) {
            $intersect = array_diff($possibleMethods, $inputMethods);
        } else {
            $intersect = array_intersect($possibleMethods, $inputMethods);
        }
        if (count($intersect) > 0) {
            $testmethods[$testcase] = array_flip($intersect);
        }
    }
}

$database = 'driver_test';

if (!isset($testmethods) || !is_array($testmethods)) {
    foreach ($testcases as $testcase) {
        $testmethods[$testcase] = array_flip(getTests($testcase));
    }
}

foreach ($dbarray as $db) {
    $dsn = $db['dsn'];
    $options = array_key_exists('options', $db) ? $db['options'] : array();
    $GLOBALS['_show_silenced'] = array_key_exists('debug', $options) ? $options['debug'] :false;

    $display_dsn = $dsn['phptype'] . "://" . $dsn['username'] . ":" . $dsn['password'] . "@" . $dsn['hostspec'] . "/" . $database;
    echo "=== Start test of $display_dsn on ".PHP_VERSION." ===\n";

    $suite = new PHPUnit_TestSuite();

    foreach ($testcases as $testcase) {
        if (is_array($testmethods[$testcase])) {
            $methods = array_keys($testmethods[$testcase]);
            foreach ($methods as $method) {
                $suite->addTest(new $testcase($method));
            }
        }
    }

    $result = new PHPUnit_TestResult;
    $result->addListener(new Console_TestListener);

    $suite->run($result);

    echo "=== End test of $display_dsn ===\n\n";
}
