<?php /* vim: se et ts=4 sw=4 sts=4 fdm=marker tw=80: */
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
 * @author   Igor Feghali <ifeghali@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  SVN: $Id$
 * @link     http://pear.php.net/packages/MDB2_Schema
 */

require_once 'PEAR/PackageFileManager2.php';

/**
 * pear channel-discover pear.vardump.org
 * pear install vd/PEAR_PackageFileManager_Git
 */
require_once 'PEAR/PackageFileManager/Git.php';

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$summary = 'XML based database schema manager'; 
$description = <<<EOT
PEAR::MDB2_Schema enables users to maintain RDBMS independant schema
files in XML that can be used to create, alter and drop database entities
and insert data into a database. Reverse engineering database schemas from
existing databases is also supported. The format is compatible with both
PEAR::MDB and Metabase.
EOT;

$version_api = '0.8.6';
$version_release = '0.8.6';
$state = 'beta';

$notes = <<<EOT
- PHP 7 support
- Composer support
- Update PHPUnit support and make unit tests more robust
- Update dependencies
- Remove renameKey()
- Add some documentation (Bug #15189)
- Improve PHPCS results (Bug #15064)
- Fix saving debug checkbox state
- Support max_identifiers_length (issue an error when identifier is longer than driver can handle)
- Tables/sequences were not being marked to be removed when new definition didn't contains tables/sequences
  * Following last release changes, sequences drops are now controlled by an option
  * BC BREAK: drop_missing_tables option became drop_obsolete_objects (tables/sequences)
- Make "sequence-on XML" element optional
- Using HTML_Template_IT for mdb2_schematool templating

open todo items:
- Clean up output of getDefinitionFromDatabase(). Sync it with Parser and Parser2.
- Automatically load reserved keywords
- Make MDB2_Schema loadable via MDB2_Driver_Common::loadModule() (Bug #8270)
- Allow simple if statements that mean that anything enclosed is only executed
  if it meets certain criterias based on that version number (or some other
  variable). This would enable people to add DML statements that are only
  executed when updating from a specific version.
- Modularize Writer code or remake it
- Add specific error codes for Validate class
- Add support for ORDER clauses on UPDATEs (to resolve the duplicate key 
  problem)
- Update description.schema.xml
- Create unit test for comparedefinitions()
- Create unit test for initializetable()
- Create unit test to compare the expected array definition with what is parsed
- Improve validateDataFieldValue() to validate <column>
- Provide more info on MDB2_Schema_Validate errors (output parsed value and expected value)
- Views support
- Fulltext index support
- PKs as constraints, not indices
- Creation of constraints only after all tables have been created/updated to avoid invalid references.
EOT;

$packagefile = './package.xml';
$options = array(
    'filelistgenerator' => 'git',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'packagefile'       => $packagefile,
    'clearcontents'     => true,
    'ignore'            => array('package.php', 'package.xml'),
    'dir_roles'         => array(
        'docs'      => 'doc',
        'examples'  => 'doc',
        'tests'     => 'test',
        'scripts'   => 'script',
        'www'       => 'www',
    ),
);
$package = &PEAR_PackageFileManager2::importOptions($packagefile, $options);

$package->setPackageType('php');
$package->setExtends('MDB2');

$package->clearDeps();
$package->setPhpDep('5.3.0');
$package->setPearInstallerDep('1.10.0');
$package->addPackageDepWithChannel(
    'required', 'MDB2', 'pear.php.net', '2.5.0b1'
);
$package->addPackageDepWithChannel(
    'required', 'XML_Parser', 'pear.php.net', '1.3.6'
);
$package->addPackageDepWithChannel(
    'optional', 'XML_DTD', 'pear.php.net', '0.5.1'
);
$package->addPackageDepWithChannel(
    'optional', 'XML_Serializer', 'pear.php.net', '0.18.0'
);
$package->addPackageDepWithChannel(
    'optional', 'HTML_Template_IT', 'pear.php.net', '1.3.0'
);

$package->addInstallAs(
    'www/mdb2_schematool/action.php', 'mdb2_schematool/action.php'
);
$package->addInstallAs(
    'www/mdb2_schematool/class.inc.php', 'mdb2_schematool/class.inc.php'
);
$package->addInstallAs(
    'www/mdb2_schematool/index.php', 'mdb2_schematool/index.php'
);
$package->addInstallAs(
    'www/mdb2_schematool/result.php', 'mdb2_schematool/result.php'
);
$package->addInstallAs(
    'scripts/mdb2_schematool', 'mdb2_schematool'
);

$package->addReplacement(
    'scripts/mdb2_schematool', 'pear-config', '@php_bin@', 'php_bin'
);

$package->updateMaintainer(
    'lead', 'lsmith', 'Lukas Kahwe Smith', 'smith@pooteeweet.org', 'no'
);
$package->updateMaintainer(
    'lead', 'ifeghali', 'Igor Feghali', 'ifeghali@php.net'
);
$package->updateMaintainer(
    'lead', 'dufuz', 'Helgi Thormar', 'dufuz@php.net', 'no'
);

$package->addRelease();
$package->setReleaseVersion($version_release);
$package->setAPIVersion($version_api);
$package->setReleaseStability($state);
$package->setAPIStability($state);
$package->setLicense(
    'BSD License', 'http://www.opensource.org/licenses/bsd-license.php'
);

$package->setNotes($notes);
$package->setSummary($summary);
$package->setDescription($description);

$package->generateContents();

if (isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
