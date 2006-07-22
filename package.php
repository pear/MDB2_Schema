<?php

require_once 'PEAR/PackageFileManager.php';

$version = 'XXX';
$notes = <<<EOT
- added new XSL and CSS for xml schema file rendering (thx Bertrand)
- automatically generate <on> tags in <sequence> tags if there is a table with
  the same name and a single column primary key
- do not dump default for LOB fields (Bug #7596)
- added support for "fixed" (needs more testing)
- phpdoc tweaks (thx Stoyan)
- fixed primary key emulation and some other minor issues in createTableIndexes() (Bug #7758)
- implemented skip_unreadable parameter in parseDatabaseDefinition() (Bug #7756)
- switched most array_key_exists() calls to !empty() to improve readability and performance
- fixed a few edge cases and potential warnings
- add method name as scope for call debug() calls
- use getValidTypes() from MDB2
- hint if dropping a primary constraint
- minor code tweak in how initialization data is set in the prepared statement
- force ISO-8859-1 when parsing XML due to different defaults for PHP4 and PHP5
- fixed handling for changes in indexes/constaints (Bug #7901)
- made forcing of defaults optional via the 'force_defaults' option (Request #8074)
- fixed several issues in the DTD (Bug #7890)
- set length and fixed for user_password in the test suite
- use nested transactions instead of normal transactions
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
$package->addMaintainer('ifeghali', 'developer', 'Igor Feghali', 'ifeghali@php.net');

$package->addDependency('php',     '4.2.0', 'ge',  'php', false);
$package->addDependency('PEAR',    '1.0b1', 'ge',  'pkg', false);
$package->addDependency('MDB2',    '2.0.1', 'ge',  'pkg', false);
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
