<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.2.0';
$notes = <<<EOT
- fixed error handling in updateDatabase()
- use MDB2::raiseError
- always copy schema file in updateDatabase()
- cosmetic fixes and tweaks
- improved overwrite to check via list*() before creating (bug #3857, #4101)
- updated MDB2 dependency
- fixed sequence dumping
- moved schema documentation, xml_reverse_engineering.php, MDB.dtd
  and MDB.xls from MDB package
- added optional support for PEAR::XML_DTD based validation of schema files
- index can be defined on fields that dont explicity prohibit null values
- dont disable sequence dumping when implicit sequences have been found
- added code to support dumping of lobs (MDB2 really should move to streams)
- added writeInitialization() method (untested)
- is_boolean() => isBoolean() in parser (CS fix)
- added MDB2_Schema::factory()
- Parser: if set grab definition of a table from the strucure property if set
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

$package->addMaintainer('lsmith', 'lead', 'Lukas Kahwe Smith', 'smith@backendmedia.com');

$package->addDependency('php',       '4.2.0', 'ge',  'php', false);
$package->addDependency('PEAR',      '1.0b1', 'ge',  'pkg', false);
$package->addDependency('MDB2', '2.0.0beta4', 'ge',  'pkg', false);
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
