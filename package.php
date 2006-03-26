<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.5.0';
$notes = <<<EOT
- expanded reserved word handling
- fix LOB data dumping
- added overwrite support to writer class
- added comment for idxname_format setting to example
- added autoincrement and primary to the documentation and .xsl/.dtd
- if we cannot create the database, then assume it was created manually in the
  test suite
- added option valid_types
- added option to set parser and writer class
- automatically let the installer set the API Version
- make sure that the Datatype module is loaded
- added valid_types property to determine if a given type is valid
  schema and to set missing default values
- allow CURRENT_* as default in temporal types (bug #6416)
- improve test suite documentation
- added parseDatabaseDefinition() that can work with a file or array definition
- removed database_definition property and as a result reworked the API of most
  methods *BC BREAK*
- added _dumpBoolean() in writer to better support variables in boolean fields
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
