<?php

require_once 'PHPUnit/Autoload.php';
require_once 'MDB2/Schema.php';
require_once dirname(__FILE__) . '/dsn.inc';

abstract class MDB2SchemaAbstract extends PHPUnit_Framework_TestCase {
    /**
     * The database name currently being tested
     * @var string
     */
    public $database;

    /**
     * The MDB2_Schema object being currently tested
     * @var MDB2_Schema
     */
    public $schema;

    /**
     * The DSN of the database that is currently being tested
     * @var array
     */
    public $dsn;

    /**
     * The unserialized value of MDB2_TEST_SERIALIZED_DSNS
     * @var array
     */
    protected static $dsns;

    /**
     * Field names of the test table
     * @var array
     */
    public $fields = array(
            'user_name'     => 'text',
            'user_password' => 'text',
            'subscribed'    => 'boolean',
            'user_id'       => 'integer',
            'quota'         => 'decimal',
            'weight'        => 'float',
            'access_date'   => 'date',
            'access_time'   => 'time',
            'approved'      => 'timestamp',
    );

    /**
     * Options to use on the current database run
     * @var array
     */
    public $options;

    //contains the name of the driver_test schema
    public $driver_input_file = 'driver_test.schema';
    //contains the name of the lob_test schema
    public $lob_input_file = 'lob_test.schema';
    //contains the name of the extension to use for backup schemas
    public $backup_extension = '.before';
    //contains the name of the extension to use for dump schemas
    public $dump_extension = '.dump';


    /**
     * Override PHPUnit's default behavior so authentication data doesn't
     * get broadcasted
     */
    protected function getDataSetAsString($strict = true) {
        return parent::getDataSetAsString(false);
    }

    public static function setUpBeforeClass() {
        $dsns = unserialize(MDB2_SCHEMA_TEST_SERIALIZED_DSNS);
        self::$dsns = $dsns;
    }

    /**
     * Produces a multi-diemnsional array containing the connection information
     * for each DBMS to be tested
     *
     * The connection information for each DBMS is an associative array with two
     * elements.  The "dsn" element must contain an array of DSN information.
     * The "options" element must be an array of connection options.
     *
     * @return array  the $dsn and $options information for MDB2::factory()
     */
    public function provider() {
        $dsns = unserialize(MDB2_SCHEMA_TEST_SERIALIZED_DSNS);
        $dbs = array();
        foreach ($dsns as $driver => $factory_params) {
            $dbs[$driver] = array(
                $factory_params,
            );
        }
        return $dbs;
    }


    /**
     * Establishes the class properties for each test
     *
     * Can not use setUp() because we are using a dataProvider to get multiple
     * MDB2 objects per test.
     *
     * @param array $fp  an associative of factory parameters.  The "dsn"
     *                   element must contain an array of DSN information.
     *                   The "options" element must be an array of connection
     *                   options.
     */
    protected function manualSetUp($fp) {
        $this->schema = MDB2_Schema::factory($fp['dsn'], $fp['options']);
        if (MDB2::isError($this->schema)) {
            $this->markTestSkipped($this->schema->getMessage());
        }
        $this->dsn = self::$dsns[$this->schema->db->phptype]['dsn'];
        $this->options = self::$dsns[$this->schema->db->phptype]['options'];
        $this->database = $this->schema->db->getDatabase();

        $this->schema->db->setDatabase($this->database);
        $this->dropTestTables();

        $this->driver_input_file = __DIR__ . "/$this->driver_input_file";
        $this->lob_input_file = __DIR__ . "/$this->lob_input_file";

        @unlink($this->driver_input_file . $this->backup_extension);
        @unlink($this->driver_input_file . $this->dump_extension);
        @unlink($this->lob_input_file . $this->backup_extension);
        @unlink($this->lob_input_file . $this->dump_extension);
    }

    public function tearDown() {
        @unlink($this->driver_input_file . $this->backup_extension);
        @unlink($this->driver_input_file . $this->dump_extension);
        @unlink($this->lob_input_file . $this->backup_extension);
        @unlink($this->lob_input_file . $this->dump_extension);

        if (!$this->schema || MDB2::isError($this->schema)) {
            return;
        }
        $this->dropTestTables();
        $this->schema->disconnect();
        unset($this->schema);
    }

    public function dropTestTables() {
        $this->schema->db->exec('DROP TABLE users');
        $this->schema->db->exec('DROP TABLE files');
    }

    public function methodExists(&$class, $name) {
        if (is_object($class)
            && in_array(strtolower($name), array_map('strtolower', get_class_methods($class)))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a result is an MDB2 error and calls the
     * appropriate PHPUnit method if it is
     *
     * + MDB2_ERROR_UNSUPPORTED: markTestSkipped(not supported)
     * + MDB2_ERROR_NOT_CAPABLE: markTestSkipped(not supported)
     * + MDB2_ERROR_NO_PERMISSION: markTestSkipped(lacks permission)
     * + MDB2_ERROR_ACCESS_VIOLATION: markTestSkipped(lacks permission)
     * + Other errors: fail(error details)
     *
     * NOTE: calling PHPUnit's skip and fail methods causes the current
     * test to halt execution, so no conditional statements or other error
     * handling are needed by this method or the test methods calling this
     * method.
     *
     * @param mixed $result   the query result to inspect
     * @param string $action  a description of what is being checked
     * @return void
     */
    public function checkResultForErrors($result, $action)
    {
        if (MDB2::isError($result)) {
            if ($result->getCode() == MDB2_ERROR_UNSUPPORTED
                || $result->getCode() == MDB2_ERROR_NOT_CAPABLE) {
                $this->markTestSkipped("$action not supported");
            }
            if ($result->getCode() == MDB2_ERROR_NO_PERMISSION
                || $result->getCode() == MDB2_ERROR_ACCESS_VIOLATION)
            {
                $this->markTestSkipped("User lacks permission to $action");
            }
            $this->fail("$action ERROR: ".$result->getUserInfo());
        }
    }
}
