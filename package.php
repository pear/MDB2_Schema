<?php

require_once 'PEAR/PackageFileManager.php';

$version = '0.1.0';
$notes = <<<EOT
This is the first unbundled release of the old MDB2_Tools_Manager class that was
previously part of MDB2 until 2.0.0beta4. Due to the name change the package
does not collide with previous versions of MDB2.

Also the following changes were made in the process:
- fixed index alteration in the Manager (bug #3710)
- fixed bug in the Manager when upgrading a database that doesnt exist
- moved logic to compareDefinitions from the Manager into the Datatype module
- removed default_values property from the Manager
  (the user will now need to set the proper defaults himself)
- do not require that not null fields have a default set in the Manager (bug #3997)
- use MDB2::raiseError() instead of MDB2_Driver_Common::raiseError()
- cleanedup connect() method to ensure that only MDB2 connections can be
  assigned to the db property
- fixed bug in connect() method that prevented overwriting of options
- several cleanups and fixes to the example.php (used to be called
  reverse_engineer_xml_schema.php)
- added apiVersion()
- use PEAR::raiseError()
EOT;

$description =<<<EOT
PEAR::MDB2_Schema enables users to maintain RDBMS independant schema files in
XML that can be used to create, alter and drop database entities and insert
data into a database. Reverse engineering database schemas frm existing
databases is also supported. The format is compatible with both PEAR::MDB
and Metabase.
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

$package->addDependency('php', '4.2.0', 'ge', 'php', false);
$package->addDependency('PEAR', '1.0b1', 'ge', 'pkg', false);
$package->addDependency('MDB2', '2.0.0beta3', 'ge', 'pkg', false);
$package->addDependency('XML_Parser', true, 'has', 'pkg', false);

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
