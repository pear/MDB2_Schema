<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Igor Feghali                           |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2_Schema enables users to maintain RDBMS independant schema files |
// | in XML that can be used to manipulate both data and database schemas |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith, Igor Feghali nor the names of his contributors may be   |
// | used to endorse or promote products derived from this software       |
// | without specific prior written permission.                           |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Igor Feghali <ifeghali@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$summary = 'XML based database schema manager'; 
$description = <<<EOT
PEAR::MDB2_Schema enables users to maintain RDBMS independant schema
files in XML that can be used to create, alter and drop database entities
and insert data into a database. Reverse engineering database schemas from
existing databases is also supported. The format is compatible with both
PEAR::MDB and Metabase.
EOT;

$version_api = '0.8.0';
$version_release = '0.8.3';
$state = 'beta';

$notes = <<<EOT
- updateDatabase() cannot add UNIQUE attribute to an existing index (Bug #13977). Patch by Holger Schletz
- updateDatabase() keeps old default value even though new column has no default (Bug #13836). Patch by Holger Schletz
- Obsolete tables and sequences not dropped on updateDatabase() (Bug #13608). Patch by Holger Schletz
- Error when creating a new index for a renamed table (Bug #13397)
- Makes use of MDB2::databaseExists() to check whether updating database exists (Bug #13073). This feature was removed on previous release and now is back again.
- createDatabase() correctly lower/upper database name when portability option deems so. 
- mdb2_schematool now disables transactions
- mdb2_schematool was missing argument "help"
- mdb2_schematool moved from "bin" to "scripts" folder. now installs to pear_bin dir
- Schema validation not failing when autoincrement field is defined but another column is used as primary key (Bug #14213)
- Accepting NOW() as value for timestamp fields on schema validation (Bug #14052). Patch by Holger Schletz
- Introducing www/mdb2_schematool that is a rewrite of docs/examples/example.php and is now installed to web root
- Web frontend (www/mdb2_schematool) has new options "DBA_username" and "DBA_password"
- Tests missing sequences on database dump (Bug #13562). Patch by Luca Corbo
- When reverse engineering a database, the XML schema file will have <charset> forced to UTF8

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
EOT;

$packagefile = './package.xml';
$options = array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'      => true,
    'baseinstalldir'    => '/',
    'packagedirectory'  => './',
    'packagefile'       => $packagefile,
    'clearcontents'     => false,
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
$package->setPhpDep('4.3.2');
$package->setPearInstallerDep('1.6.0');
$package->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '2.5.0b1');
$package->addPackageDepWithChannel('required', 'XML_Parser', 'pear.php.net', '1.2.8');
$package->addPackageDepWithChannel('optional', 'XML_DTD', 'pear.php.net', '0.4.2');
$package->addPackageDepWithChannel('optional', 'XML_Serializer', 'pear.php.net', '0.18.0');

$package->addInstallAs('www/mdb2_schematool/action.php',    'mdb2_schematool/action.php');
$package->addInstallAs('www/mdb2_schematool/class.inc.php', 'mdb2_schematool/class.inc.php');
$package->addInstallAs('www/mdb2_schematool/index.php',     'mdb2_schematool/index.php');
$package->addInstallAs('www/mdb2_schematool/result.php',    'mdb2_schematool/result.php');

$package->updateMaintainer('lead', 'lsmith', 'Lukas Kahwe Smith', 'smith@pooteeweet.org', 'no');
$package->updateMaintainer('lead', 'ifeghali', 'Igor Feghali', 'ifeghali@php.net');
$package->updateMaintainer('lead', 'dufuz', 'Helgi Thormar', 'dufuz@php.net');
$package->updateMaintainer('contributor', 'fornax', 'Andrew Hill', 'andrew-pear@fornax.net', 'no');
$package->updateMaintainer('helper', 'lsolesen', 'Lars Olesen', 'lars@legestue.net', 'no');
$package->updateMaintainer('contributor', 'afz', 'Ali Fazelzadeh', 'afz@dev-code.com');

$package->addRelease();
$package->setReleaseVersion($version_release);
$package->setAPIVersion($version_api);
$package->setReleaseStability($state);
$package->setAPIStability($state);
$package->setLicense('BSD License', 'http://www.opensource.org/licenses/bsd-license.php');

$package->setNotes($notes);
$package->setSummary($summary);
$package->setDescription($description);

$package->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

?>
