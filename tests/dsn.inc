<?php

/**
 * Defines DSN's for all databases that have <DBMS>_TEST_* environment
 * variables set and have their corresponding extensions loaded
 *
 * <DBMS>_TEST_USER, <DBMS>_MYSQL_TEST_PASSWD and <DBMS>_TEST_DB are required,
 * except for SQLite.
 *
 * SQLite runs by default as long as the extension is loaded.  The tests use
 * :memory: as the database unless the SQLITE_TEST_DB environment variable is
 * defined.
 *
 * <DBMS> can be one or more of the following:
 *   + FBSQL
 *   + IBASE
 *   + MSSQL
 *   + MYSQL (for mysql and mysqli extensions)
 *   + OCI8
 *   + PGSQL
 *   + SQLITE
 *   + SQLSRV
 *
 * Examine this file to see exactly which settings are available for
 * the DBMS's you are interested in testing.
 *
 * In the event you do NOT want a particular driver tested but you have that
 * extension installed in PHP and have the requisite environment variables set,
 * that driver's tests can be disabled by UN-commenting the corresponding
 * entry in the $skip array at the top of this script.
 *
 * The resulting DSN's are passed to serialize() and stored in the
 * MDB2_TEST_SERIALIZED_DSNS constant.
 *
 * @package MDB2
 * @category Database
 * @author Daniel Convissor <danielc@php.net>
 */


// See description in the docblock, above.
$skip = array(
//    'fbsql',
//    'ibase',
//    'mssql',
//    'mysql',
//    'mysqli',
//    'oci8',
//    'pgsql',
//    'sqlite',
//    'sqlsrv',
);


$dsns = array();

if (getenv('FBSQL_TEST_USER') && !in_array('fbsql', $skip)
    && is_driver_available('fbsql') && extension_loaded('fbsql'))
{
    $dsns['fbsql'] = array(
        'dsn' => array(
            'phptype' => 'fbsql',
            'username' => getenv('FBSQL_TEST_USER'),
            'password' => getenv('FBSQL_TEST_PASSWD'),
            'database' => getenv('FBSQL_TEST_DB'),

            'hostspec' => !getenv('FBSQL_TEST_HOST')
                    ? null : getenv('FBSQL_TEST_HOST'),
        ),
        'options' => array(
        ),
    );
}

if (getenv('IBASE_TEST_USER') && !in_array('ibase', $skip)
    && is_driver_available('ibase') && extension_loaded('interbase'))
{
    $dsns['ibase'] = array(
        'dsn' => array(
            'phptype' => 'ibase',
            'username' => getenv('IBASE_TEST_USER'),
            'password' => getenv('IBASE_TEST_PASSWD'),
            'database' => getenv('IBASE_TEST_DB'),

            'hostspec' => !getenv('IBASE_TEST_HOST')
                    ? null : getenv('IBASE_TEST_HOST'),

            'buffers' => !getenv('IBASE_TEST_BUFFERS')
                    ? null : getenv('IBASE_TEST_BUFFERS'),

            'dialect' => !getenv('IBASE_TEST_DIALECT')
                    ? null : getenv('IBASE_TEST_DIALECT'),

            'role' => !getenv('IBASE_TEST_ROLE')
                    ? null : getenv('IBASE_TEST_ROLE'),

            'dbsyntax' => !getenv('IBASE_TEST_SYNTAX')
                    ? null : getenv('IBASE_TEST_SYNTAX'),
        ),
        'options' => array(
        ),
    );
}

if (getenv('MSSQL_TEST_USER') && !in_array('mssql', $skip)
    && is_driver_available('mssql') && function_exists('mssql_connect'))
{
    $dsns['mssql'] = array(
        'dsn' => array(
            'phptype' => 'mssql',
            'username' => getenv('MSSQL_TEST_USER'),
            'password' => getenv('MSSQL_TEST_PASSWD'),
            'database' => getenv('MSSQL_TEST_DB'),

            'hostspec' => !getenv('MSSQL_TEST_HOST')
                    ? null : getenv('MSSQL_TEST_HOST'),

            'port' => !getenv('MSSQL_TEST_PORT')
                    ? null : getenv('MSSQL_TEST_PORT'),
        ),
        'options' => array(
        ),
    );
}

if (getenv('MYSQL_TEST_USER') && !in_array('mysql', $skip)
    && is_driver_available('mysql') && extension_loaded('mysql'))
{
    $dsns['mysql'] = array(
        'dsn' => array(
            'phptype' => 'mysql',
            'username' => getenv('MYSQL_TEST_USER'),
            'password' => getenv('MYSQL_TEST_PASSWD'),
            'database' => getenv('MYSQL_TEST_DB'),

            'hostspec' => !getenv('MYSQL_TEST_HOST')
                    ? null : getenv('MYSQL_TEST_HOST'),

            'port' => !getenv('MYSQL_TEST_PORT')
                    ? null : getenv('MYSQL_TEST_PORT'),

            'socket' => !getenv('MYSQL_TEST_SOCKET')
                    ? null : getenv('MYSQL_TEST_SOCKET'),

            // Hack for MDB2's silly connect method logic.
            'protocol' => !getenv('MYSQL_TEST_SOCKET')
                    ? null : 'unix',
        ),
        'options' => array(
            'use_transactions' => true
        ),
    );
}

if (getenv('MYSQL_TEST_USER') && !in_array('mysqli', $skip)
    && is_driver_available('mysqli') && extension_loaded('mysqli'))
{
    $dsns['mysqli'] = array(
        'dsn' => array(
            'phptype' => 'mysqli',
            'username' => getenv('MYSQL_TEST_USER'),
            'password' => getenv('MYSQL_TEST_PASSWD'),
            'database' => getenv('MYSQL_TEST_DB'),

            'hostspec' => !getenv('MYSQL_TEST_HOST')
                    ? null : getenv('MYSQL_TEST_HOST'),

            'port' => !getenv('MYSQL_TEST_PORT')
                    ? null : getenv('MYSQL_TEST_PORT'),

            'socket' => !getenv('MYSQL_TEST_SOCKET')
                    ? null : getenv('MYSQL_TEST_SOCKET'),
        ),
        'options' => array(
            'use_transactions' => true
        ),
    );
}

if (getenv('OCI8_TEST_USER') && !in_array('oci8', $skip)
    && is_driver_available('oci8') && extension_loaded('oci8'))
{
    $dsns['oci8'] = array(
        'dsn' => array(
            'phptype' => 'oci8',
            'username' => !getenv('OCI8_TEST_USER')
                    ? null : getenv('OCI8_TEST_USER'),

            'password' => !getenv('OCI8_TEST_PASSWD')
                    ? null : getenv('OCI8_TEST_PASSWD'),

            'database' => !getenv('OCI8_TEST_DB')
                    ? null : getenv('OCI8_TEST_DB'),

            'hostspec' => !getenv('OCI8_TEST_HOST')
                    ? null : getenv('OCI8_TEST_HOST'),

            'port' => !getenv('OCI8_TEST_PORT')
                    ? null : getenv('OCI8_TEST_PORT'),

            'service' => !getenv('OCI8_TEST_SERVICE')
                    ? null : getenv('OCI8_TEST_SERVICE'),
        ),
        'options' => array(
        ),
    );
}

if (getenv('PGSQL_TEST_USER') && !in_array('pgsql', $skip)
    && is_driver_available('pgsql') && extension_loaded('pgsql'))
{
    $dsns['pgsql'] = array(
        'dsn' => array(
            'phptype' => 'pgsql',
            'username' => getenv('PGSQL_TEST_USER'),
            'password' => getenv('PGSQL_TEST_PASSWD'),
            'database' => getenv('PGSQL_TEST_DB'),

            'hostspec' => !getenv('PGSQL_TEST_HOST')
                    ? null : getenv('PGSQL_TEST_HOST'),

            'port' => !getenv('PGSQL_TEST_PORT')
                    ? null : getenv('PGSQL_TEST_PORT'),

            'socket' => !getenv('PGSQL_TEST_SOCKET')
                    ? null : getenv('PGSQL_TEST_SOCKET'),

            'protocol' => !getenv('PGSQL_TEST_PROTOCOL')
                    ? null : getenv('PGSQL_TEST_PROTOCOL'),

            'options' => !getenv('PGSQL_TEST_OPTIONS')
                    ? null : getenv('PGSQL_TEST_OPTIONS'),

            'tty' => !getenv('PGSQL_TEST_TTY')
                    ? null : getenv('PGSQL_TEST_TTY'),

            'connect_timeout' => !getenv('PGSQL_TEST_CONNECT_TIMEOUT')
                    ? null : getenv('PGSQL_TEST_CONNECT_TIMEOUT'),

            'sslmode' => !getenv('PGSQL_TEST_SSL_MODE')
                    ? null : getenv('PGSQL_TEST_SSL_MODE'),

            'service' => !getenv('PGSQL_TEST_SERVICE')
                    ? null : getenv('PGSQL_TEST_SERVICE'),
        ),
        'options' => array(
        ),
    );
}

// SQLite is different.  Runs using :memory: table even if variable is not set.
if (!in_array('sqlite', $skip)
    && is_driver_available('sqlite') && extension_loaded('sqlite'))
{
    if (!getenv('SQLITE_TEST_DB')) {
        $dsns['sqlite'] = array(
            'dsn' => array(
                'phptype' => 'sqlite',
                'database' => ':memory:',
            ),
            'options' => array(
            ),
        );
    } else {
        $dsns['sqlite'] = array(
            'dsn' => array(
                'phptype' => 'sqlite',
                'database' => getenv('SQLITE_TEST_DB'),
            ),
            'options' => array(
            ),
        );
    }
}

if (getenv('SQLSRV_TEST_USER') && !in_array('sqlsrv', $skip)
    && is_driver_available('sqlsrv') && extension_loaded('sqlsrv'))
{
    $dsns['sqlsrv'] = array(
        'dsn' => array(
            'phptype' => 'sqlsrv',
            'username' => getenv('SQLSRV_TEST_USER'),
            'password' => getenv('SQLSRV_TEST_PASSWD'),
            'database' => getenv('SQLSRV_TEST_DB'),

            'hostspec' => !getenv('SQLSRV_TEST_HOST')
                    ? null : getenv('SQLSRV_TEST_HOST'),

            'port' => !getenv('SQLSRV_TEST_PORT')
                    ? null : getenv('SQLSRV_TEST_PORT'),
        ),
        'options' => array(
        ),
    );
}


// Just making sure to avoid fatal errors if the file gets included twice.
if (!defined('MDB2_SCHEMA_TEST_SERIALIZED_DSNS')) {
    /**
     * A serialize()'ed array of DSN's for all active databases
     */
    define('MDB2_SCHEMA_TEST_SERIALIZED_DSNS', serialize($dsns));
}

/**
 * Determines if the desired MDB2_Driver class is available
 *
 * @param string $phptype  the "phptype" of the driver we're looking for
 * @return bool
 */
function is_driver_available($phptype) {
    if ($fp = @fopen("MDB2/Driver/$phptype.php", 'r', true)) {
        fclose($fp);
        return true;
    } else {
        return false;
    }
}
