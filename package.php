<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.4.0';
$notes = <<<EOT
- Silence a "error" when there is auto increment and a primary key index defined
  on the same field, since auto increment implies a primary key
- support column length in create index (mysql only feature, but a nice touch,
  emulating it with substring is not feasible though)
- use exec() for INSERT statement
- add extra debug info better use of the var_dump package if present in example
- fixed bug in verifyAlterDatabase() when checking table alterations (bug #5977)
- fixed typo in getTableConstraintDefinition() method name (removed additional "s") (bug #6054)
- expect and ignore MDB2_ERROR_NOT_FOUND when calling getTableConstraintDefinition()
  and getTableIndexDefinition() (bug #6055)
- fixed typo in verifyAlterDatabase() (bug #6053)
- fixed typo in alterDatabaseSequences() (bug #6053)
- added test suite
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
        ),
    )
);

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('lsmith', 'lead', 'Lukas Kahwe Smith', 'smith@pooteeweet.org');

$package->addDependency('php',       '4.2.0', 'ge',  'php', false);
$package->addDependency('PEAR',      '1.0b1', 'ge',  'pkg', false);
$package->addDependency('MDB2', '2.0.0beta7', 'ge',  'pkg', false);
$package->addDependency('XML_Parser',   true, 'has', 'pkg', false);
$package->addDependency('XML_DTD',      true, 'has', 'pkg', true);

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
