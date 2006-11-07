<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$version = '1.0.0';
$version_api = '0.7.0';
$version_release = '0.7.0';
$state = 'beta';

$notes = <<<EOT
- Improved INSERT support, added UPDATE and DELETE statements
- XML syntax redesigned to cover DML addictions
- Creation of an explicit NULL (<null></null>)
- various fixes to the DTD, including DML addictions
- XSD schema definition created
- Tests also output php version
- New example parse.php lets you check how a XML schema is stored
- Updated schema.xml to demo the DML support
- Documentation reformulated, also covering new XML syntax
- Improved demo script example.php, which now includes more actions and options
- Writer is returning an error when fopen() fails
- Writer has now its own error code
- Variable "seq" became "sequence" and "seq_name" became "sequence_name" in the
  Parser
- Moved validation code out of the Parser into a new separate class called
  Validation
- Fixed index handling in the Parser
- Refactored error handling in the Parser
- Removed variable "init_field", that wasn't being used, from the Parser
- Parser simulates only one level of recurssion, which means no more than 
  function-expression or vice-versa
- Fixed warning due to not checking with isset() in the Parser
- Fixed warning due to not checking with isset() in the Schema
- Typo fix (related to Bug #9024)
- Fixed createDatabase() that was trying to use a non-existant database that 
  was about to be created
- Simplified API for compareTableFieldsDefinitions() and 
  compareTableIndexesDefinitions()
- Rewritten some docblocks
- Added fold markers where missing
- Removed @static from non static methods
- Fixed several PEAR CS issues
- Added code for field/identifier quoting
- Ensure all identifiers are passed to quoteIdentifier() (Bug #8429)

open todo items:
- Make MDB2_Schema loadable via MDB2_Driver_Common::loadModule() (Bug #8270)
- Add ability to define variables inside the schema (like a version number)
- Allow simple if statements that mean that anything enclosed is only executed
  if it meets certain criterias based on that version number (or some other
  variable). This would enable people to add DML statements that are only
  executed when updating from a specific version.
- Add support for recursive tag parsing. Currently only expression<->function
  is supported but not expression-expression or function-function, although
  recursion is already supported by the initializeTable() method
- Parser should be replaced for XML serializer ?
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
- Add optional support for scale in decimal fields
- Implement "WHERE column IS NULL"
- Add ability to parse a "contents-only" XML dump
- Improve validateDataFieldValue() to validate <column>
- Provide more info on MDB2_Schema_Validate errors (output parsed value and expected value)
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
