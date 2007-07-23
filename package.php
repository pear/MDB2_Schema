<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = '1.0.0';
$version_api = '0.7.2';
$version_release = '0.7.2';
$state = 'beta';

$notes = <<<EOT
- Allow options to be passed in when creating tables (Bug #10278)
- Existence of a single index results in no further index creation (Bug #10296)
- MDB2_Schema::createTableIndexes() ignores indexes (Bug #10285)
- column identifiers quoting problem (Bug #10195)
- Handle 'scale' value in DECIMAL field definition (Bug #10475)
- fixed phpdoc comment for validateDataField()
- all primary keys are given an index name of "primary" (Bug #10457)
- example.php should echo xml when no filename is given (Bug #10226)

open todo items:
- Automatically load reserved keywords
- Make MDB2_Schema loadable via MDB2_Driver_Common::loadModule() (Bug #8270)
- Add ability to define variables inside the schema (like a version number)
- Allow simple if statements that mean that anything enclosed is only executed
  if it meets certain criterias based on that version number (or some other
  variable). This would enable people to add DML statements that are only
  executed when updating from a specific version.
- Modularize Writer code or remake it
- Add specific error codes for Validate class
- Add support for ORDER clauses on UPDATEs (to resolve the duplicate key 
  problem)
- Update description.schema.xml
- Document how to use the API
- Create unit test for comparedefinitions()
- Create unit test for initializetable()
- Create unit test to compare the expected array definition with what is parsed
- HTML entities aren't being parsed correctly
- Improve validateDataFieldValue() to validate <column>
- Provide more info on MDB2_Schema_Validate errors (output parsed value and expected value)
- Views support
- Foreign keys support
- Fulltext index support
EOT;

$description = <<<EOT
PEAR::MDB2_Schema enables users to maintain RDBMS independant schema
files in XML that can be used to create, alter and drop database entities
and insert data into a database. Reverse engineering database schemas from
existing databases is also supported. The format is compatible with both
PEAR::MDB and Metabase.
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
         'examples' => 'doc',
         'tests'    => 'test',
    ),
);

$package = &PEAR_PackageFileManager2::importOptions($packagefile, $options);
$package->setPackageType('php');
$package->setExtends('MDB2');
$package->addRelease();
$package->generateContents();
$package->setReleaseVersion($version_release);
$package->setAPIVersion($version_api);
$package->setReleaseStability($state);
$package->setAPIStability($state);
$package->setNotes($notes);
$package->setDescription($description);
$package->addGlobalReplacement('package-info', '@package_version@', 'version');

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
