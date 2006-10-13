<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.7.0';
$notes = <<<EOT
- various fixes to the DTD
- added new XSD schema definition
- xml schema documentation updated
- added new example parse.php/schema.xml to demo the DML support
- improved main functionalities demo, example.php
- added support for DML statements (UPDATE, DELETE, improved INSERT)
- moved validation code out of the parser into a separate class
- created an error code for writer
- returning an error when fopen() fails in the writer
- fixed warning due to not checking with isset() in the parser
- ensure all identifiers are passed to quoteIdentifier() (Bug #8429)

open todo items:
- make MDB2_Schema loadable via MDB2_Driver_Common::loadModule() (Bug #8270)
- add ability to define variables inside the schema (like a version number)
- allow simple if statements that mean that anything enclosed is only executed
  if it meets certain criterias based on that version number (or some other
  variable). this would enable people to add DML statements that are only
  executed when updating from a specific version.
- add support for recursive tag parsing. Currently only expression-function
  is supported but not expression-expression or function-function, although
  recursion is already supported by the initializeTable method
- parser should be replaced by XML serializer ?
- modularize writer code or remake it
- add specific error codes for validate class
- add support for ORDER clauses on UPDATEs (to resolve the duplicate key problem)
- update description.schema.xml
- document how to use the API
- create unit test for comparedefinitions
- create unit test for initializetable
- create unit test to compare the expected array definition with what is parsed
- bind a parsed empty value to NULL or ''. currently it is generating a query error when creating a table from file
- HTML entities aren't being parsed correctly
EOT;

$description =<<<EOT
PEAR::MDB2_Schema enables users to maintain RDBMS independant schema
files in XML that can be used to create, alter and drop database entities
and insert data into a database. Reverse engineering database schemas from
existing databases is also supported. The format is compatible with both
PEAR::MDB and Metabase.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(
    array(
        'package'           => 'MDB2_Schema',
        'summary'           => 'XML based database schema manager',
        'description'       => $description,
        'version'           => $version,
        'state'             => 'beta',
        'license'           => 'BSD License',
        'filelistgenerator' => 'cvs',
        'ignore'            => array('package.php', 'package.xml'),
        'notes'             => $notes,
        'changelogoldtonew' => false,
        'simpleoutput'      => true,
        'baseinstalldir'    => '/',
        'packagedirectory'  => './',
        'dir_roles'         => array(
            'docs' => 'doc',
             'examples' => 'doc',
             'tests' => 'test',
        ),
    )
);

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('lsmith', 'lead', 'Lukas Kahwe Smith', 'smith@pooteeweet.org');
$package->addMaintainer('ifeghali', 'lead', 'Igor Feghali', 'ifeghali@php.net');

$package->addDependency('php',     '4.2.0', 'ge',  'php', false);
$package->addDependency('PEAR',    '1.0b1', 'ge',  'pkg', false);
$package->addDependency('MDB2',    '2.2.0', 'ge',  'pkg', false);
$package->addDependency('XML_Parser', true, 'has', 'pkg', false);
$package->addDependency('XML_DTD',    true, 'has', 'pkg', true);

$package->addglobalreplacement('package-info', '@package_version@', 'version');

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
