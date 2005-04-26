<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@backendmedia.com>                         |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'MDB2.php';

define('MDB2_MANAGER_DUMP_ALL',          0);
define('MDB2_MANAGER_DUMP_STRUCTURE',    1);
define('MDB2_MANAGER_DUMP_CONTENT',      2);

/**
 * The database manager is a class that provides a set of database
 * management services like installing, altering and dumping the data
 * structures of databases.
 *
 * @package MDB2_Schema
 * @category Database
 * @author  Lukas Smith <smith@backendmedia.com>
 */
class MDB2_Schema extends PEAR
{
    // {{{ properties

    var $db;

    var $warnings = array();

    var $options = array(
        'fail_on_invalid_names' => true,
        'dtd_file' => false,
    );

    var $database_definition = array(
        'name' => '',
        'create' => 0,
        'tables' => array()
    );

    // }}}
    // {{{ apiVersion()

    /**
     * Return the MDB2 API version
     *
     * @return string     the MDB2 API version number
     * @access public
     */
    function apiVersion()
    {
        return '@package_version@';
    }

    // }}}
    // {{{ raiseError()

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param mixed $code integer error code, or a PEAR error object (all
     *      other parameters are ignored if this parameter is an object
     * @param int $mode error mode, see PEAR_Error docs
     * @param mixed $options If error mode is PEAR_ERROR_TRIGGER, this is the
     *      error level (E_USER_NOTICE etc).  If error mode is
     *      PEAR_ERROR_CALLBACK, this is the callback function, either as a
     *      function name, or as an array of an object and method name. For
     *      other error modes this parameter is ignored.
     * @param string $userinfo Extra debug information.  Defaults to the last
     *      query and native error code.
     * @param mixed $nativecode Native error code, integer or string depending
     *      the backend.
     * @return object a PEAR error object
     * @access public
     * @see PEAR_Error
     */
    function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        return MDB2::raiseError($code, $mode, $options, $userinfo);
    }

    // }}}
    // {{{ resetWarnings()

    /**
     * reset the warning array
     *
     * @access public
     */
    function resetWarnings()
    {
        $this->warnings = array();
    }

    // }}}
    // {{{ getWarnings()

    /**
     * get all warnings in reverse order.
     * This means that the last warning is the first element in the array
     *
     * @return array with warnings
     * @access public
     * @see resetWarnings()
     */
    function getWarnings()
    {
        return array_reverse($this->warnings);
    }

    // }}}
    // {{{ setOption()

    /**
     * set the option for the db class
     *
     * @param string $option option name
     * @param mixed $value value for the option
     * @return mixed MDB2_OK or MDB2 Error Object
     * @access public
     */
    function setOption($option, $value)
    {
        if (isset($this->options[$option])) {
            if (is_null($value)) {
                return $this->raiseError(MDB2_ERROR, null, null,
                    'may not set an option to value null');
            }
            $this->options[$option] = $value;
            return MDB2_OK;
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            "unknown option $option");
    }

    // }}}
    // {{{ getOption()

    /**
     * returns the value of an option
     *
     * @param string $option option name
     * @return mixed the option value or error object
     * @access public
     */
    function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return $this->raiseError(MDB2_ERROR_UNSUPPORTED,
            null, null, "unknown option $option");
    }

    // }}}
    // {{{ connect()

    /**
     * Create a new MDB2 connection object and connect to the specified
     * database
     *
     * @param   mixed   $db       'data source name', see the MDB2::parseDSN
     *                            method for a description of the dsn format.
     *                            Can also be specified as an array of the
     *                            format returned by MDB2::parseDSN.
     *                            Finally you can also pass an existing db
     *                            object to be used.
     * @param   mixed   $options  An associative array of option names and
     *                            their values.
     * @return  mixed MDB2_OK on success, or a MDB2 error object
     * @access  public
     * @see     MDB2::parseDSN
     */
    function connect(&$db, $options = array())
    {
        $db_options = array();
        if (is_array($options) && !empty($options)) {
            foreach ($options as $option => $value) {
                if (array_key_exists($option, $this->options)) {
                    $err = $this->setOption($option, $value);
                    if (PEAR::isError($err)) {
                        return $err;
                    }
                } else {
                    $db_options[$option] = $value;
                }
            }
        }
        $this->disconnect();
        if (!MDB2::isConnection($db)) {
            $db =& MDB2::connect($db, $db_options);
        }
        if (PEAR::isError($db)) {
            return $db;
        }
        $this->db =& $db;
        $this->db->loadModule('Manager');
        return MDB2_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @access public
     */
    function disconnect()
    {
        if (MDB2::isConnection($this->db)) {
            $this->db->disconnect();
            unset($this->db);
        }
    }

    // }}}
    // {{{ parseDatabaseDefinitionFile()

    /**
     * Parse a database definition file by creating a Metabase schema format
     * parser object and passing the file contents as parser input data stream.
     *
     * @param string $input_file the path of the database schema file.
     * @param array $variables an associative array that the defines the text
     * string values that are meant to be used to replace the variables that are
     * used in the schema description.
     * @param bool $fail_on_invalid_names (optional) make function fail on invalid
     * names
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function parseDatabaseDefinitionFile($input_file, $variables = array(),
        $fail_on_invalid_names = true, $structure = false)
    {
        $dtd_file = $this->getOption('dtd_file');
        if ($dtd_file) {
            require_once 'XML/DTD/XmlValidator.php';
            $dtd =& new XML_DTD_XmlValidator;
            if (!$dtd->isValid($dtd_file, $input_file)) {
                return $this->raiseError(MDB2_ERROR_MANAGER_PARSE, null, null, $dtd->getMessage());
            }
        }

        require_once 'MDB2/Schema/Parser.php';
        $parser =& new MDB2_Schema_Parser($variables, $fail_on_invalid_names, $structure);
        $result = $parser->setInputFile($input_file);
        if (PEAR::isError($result)) {
            return $result;
        }
        $result = $parser->parse();
        if (PEAR::isError($result)) {
            return $result;
        }
        if (PEAR::isError($parser->error)) {
            return $parser->error;
        }
        return $parser->database_definition;
    }

    // }}}
    // {{{ getDefinitionFromDatabase()

    /**
     * Attempt to reverse engineer a schema structure from an existing MDB2
     * This method can be used if no xml schema file exists yet.
     * The resulting xml schema file may need some manual adjustments.
     *
     * @return mixed MDB2_OK or array with all ambiguities on success, or a MDB2 error object
     * @access public
     */
    function getDefinitionFromDatabase()
    {
        $this->db->loadModule('Reverse');
        $database = $this->db->database_name;
        if (empty($database)) {
            return $this->raiseError('it was not specified a valid database name');
        }

        $this->database_definition = array(
            'name' => $database,
            'create' => 1,
            'tables' => array(),
        );

        $tables = $this->db->manager->listTables();
        if (PEAR::isError($tables)) {
            return $tables;
        }

        foreach ($tables as $table_name) {
            $fields = $this->db->manager->listTableFields($table_name);
            if (PEAR::isError($fields)) {
                return $fields;
            }
            $this->database_definition['tables'][$table_name] = array('fields' => array());
            $table_definition =& $this->database_definition['tables'][$table_name];
            foreach ($fields as $field_name) {
                $definition = $this->db->reverse->getTableFieldDefinition($table_name, $field_name);
                if (PEAR::isError($definition)) {
                    return $definition;
                }
                $table_definition['fields'][$field_name] = $definition[0][0];
                $field_choices = count($definition[0]);
                if ($field_choices > 1) {
                    $warning = "There are $field_choices type choices in the table $table_name field $field_name (#1 is the default): ";
                    $field_choice_cnt = 1;
                    $table_definition['fields'][$field_name]['choices'] = array();
                    foreach ($definition[0] as $field_choice) {
                        $table_definition['fields'][$field_name]['choices'][] = $field_choice;
                        $warning .= 'choice #'.($field_choice_cnt).': '.serialize($field_choice);
                        $field_choice_cnt++;
                    }
                    $this->warnings[] = $warning;
                }
                if (isset($definition[1])) {
                    $sequence = $definition[1]['definition'];
                    $sequence_name = $definition[1]['name'];
                    $this->db->debug('Implicitly defining sequence: '.$sequence_name);
                    if (!isset($this->database_definition['sequences'])) {
                        $this->database_definition['sequences'] = array();
                    }
                    $this->database_definition['sequences'][$sequence_name] = $sequence;
                }
                if (isset($definition[2])) {
                    $index = $definition[2]['definition'];
                    $index_name = $definition[2]['name'];
                    $this->db->debug('Implicitly defining index: '.$index_name);
                    if (!isset($table_definition['indexes'])) {
                        $table_definition['indexes'] = array();
                    }
                    $table_definition['indexes'][$index_name] = $index;
                }
            }
            $indexes = $this->db->manager->listTableIndexes($table_name);
            if (PEAR::isError($indexes)) {
                return $indexes;
            }
            if (is_array($indexes) && !empty($indexes)
                && !isset($table_definition['indexes'])
            ) {
                $table_definition['indexes'] = array();
                foreach ($indexes as $index_name) {
                    $definition = $this->db->reverse->getTableIndexDefinition($table_name, $index_name);
                    if (PEAR::isError($definition)) {
                        return $definition;
                    }
                   $table_definition['indexes'][$index_name] = $definition;
                }
            }
        }

        $sequences = $this->db->manager->listSequences();
        if (PEAR::isError($sequences)) {
            return $sequences;
        }
        if (is_array($sequences) && !empty($sequences)) {
            $this->database_definition['sequences'] = array();
            foreach ($sequences as $sequence_name) {
                $definition = $this->db->reverse->getSequenceDefinition($sequence_name);
                if (PEAR::isError($definition)) {
                    return $definition;
                }
                $this->database_definition['sequences'][$sequence_name] = $definition;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTableIndexes()

    /**
     * create a indexes om a table
     *
     * @param string $table_name  name of the table
     * @param array  $indexes     indexes to be created
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @param boolean $overwrite  determine if the table/index should be
                                  overwritten if it already exists
     * @access public
     */
    function createTableIndexes($table_name, $indexes, $overwrite)
    {
        if (!$this->db->supports('indexes')) {
            $this->db->debug('Indexes are not supported');
            return MDB2_OK;
        }
        foreach ($indexes as $index_name => $index) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $indexes = $this->db->manager->listTableIndexes($table_name);
            $this->db->popExpect();
            if (PEAR::isError($indexes)) {
                if (!MDB2::isError($indexes, $errorcodes)) {
                    return $indexes;
                }
            } elseif (is_array($indexes) && in_array($index_name, $indexes)) {
                if (!$overwrite) {
                    $this->db->debug('Index already exists: '.$index_name);
                    return MDB2_OK;
                }
                $result = $this->db->manager->dropIndex($table_name, $index_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $this->db->debug('Overwritting index: '.$index_name);
            }
            $result = $this->db->manager->createIndex($table_name, $index_name, $index);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ createTable()

    /**
     * create a table and inititialize the table if data is available
     *
     * @param string $table_name  name of the table to be created
     * @param array  $table       multi dimensional array that containts the
     *                            structure and optional data of the table
     * @param boolean $overwrite  determine if the table/index should be
                                  overwritten if it already exists
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createTable($table_name, $table, $overwrite = false)
    {
        $create = true;
        $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
        $this->db->expectError($errorcodes);
        $tables = $this->db->manager->listTables();
        $this->db->popExpect();
        if (PEAR::isError($tables)) {
            if (!MDB2::isError($tables, $errorcodes)) {
                return $tables;
            }
        } elseif (is_array($tables) && in_array($table_name, $tables)) {
            if (!$overwrite) {
                $create = false;
                $this->db->debug('Table already exists: '.$table_name);
            } else {
                $result = $this->db->manager->dropTable($table_name);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $this->db->debug('Overwritting table: '.$table_name);
            }
        }
        if ($create) {
            $result = $this->db->manager->createTable($table_name, $table['fields']);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        if (isset($table['initialization']) && is_array($table['initialization'])) {
            $result = $this->initializeTable($table_name, $table);
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        if (isset($table['indexes']) && is_array($table['indexes'])) {
            $result = $this->createTableIndexes($table_name, $table['indexes'], $overwrite);
            if (PEAR::isError($result)) {
                return $result;
            }
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ initializeTable()

    /**
     * inititialize the table with data
     *
     * @param string $table_name        name of the table
     * @param array  $table       multi dimensional array that containts the
     *                            structure and optional data of the table
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function initializeTable($table_name, $table)
    {
        foreach ($table['fields'] as $field_name => $field) {
            $placeholders[$field_name] = ':'.$field_name;
            $types[$field_name] = $field['type'];
        }
        $fields = implode(',', array_keys($table['fields']));
        $placeholders = implode(',', $placeholders);
        $query = "INSERT INTO $table_name ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($query, $types);
        if (PEAR::isError($stmt)) {
            return $stmt;
        }
        foreach ($table['initialization'] as $instruction) {
            switch ($instruction['type']) {
            case 'insert':
                if (isset($instruction['fields']) && is_array($instruction['fields'])) {
                    $result = $stmt->bindParamArray($instruction['fields']);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $result = $stmt->execute();
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
                break;
            }
        }
        return $stmt->free();
    }

    // }}}
    // {{{ createSequence()

    /**
     * create a sequence
     *
     * @param string $sequence_name  name of the sequence to be created
     * @param array  $sequence       multi dimensional array that containts the
     *                               structure and optional data of the table
     * @param boolean $overwrite    determine if the sequence should be overwritten
                                    if it already exists
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createSequence($sequence_name, $sequence, $overwrite = false)
    {
        if (!$this->db->supports('sequences')) {
            $this->db->debug('Sequences are not supported');
            return MDB2_OK;
        }

        $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
        $this->db->expectError($errorcodes);
        $sequences = $this->db->manager->listSequences();
        $this->db->popExpect();
        if (PEAR::isError($sequences)) {
            if (!MDB2::isError($sequences, $errorcodes)) {
                return $sequences;
            }
        } elseif (is_array($sequence) && in_array($sequence_name, $sequences)) {
            if (!$overwrite) {
                $this->db->debug('Sequence already exists: '.$sequence_name);
                return MDB2_OK;
            }
            $result = $this->db->manager->dropSequence($sequence_name);
            if (PEAR::isError($result)) {
                return $result;
            }
            $this->db->debug('Overwritting sequence: '.$sequence_name);
        }

        $start = 1;
        if (isset($sequence['on'])) {
            $table = $sequence['on']['table'];
            $field = $sequence['on']['field'];
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $tables = $this->db->manager->listTables();
            $this->db->popExpect();
            if (PEAR::isError($tables) && !MDB2::isError($tables, $errorcodes)) {
                 return $tables;
            }
            if (PEAR::isError($tables) ||
                (is_array($table) && in_array($table, $tables))
            ) {
                if ($this->db->supports('summary_functions')) {
                    $query = "SELECT MAX($field) FROM $table";
                } else {
                    $query = "SELECT $field FROM $table ORDER BY $field DESC";
                }
                $start = $this->db->queryOne($query, 'integer');
                if (PEAR::isError($start)) {
                    return $start;
                }
                ++$start;
            } else {
                $this->warnings[] = 'Could not sync sequence: '.$sequence_name;
            }
        } elseif (isset($sequence['start']) && is_numeric($sequence['start'])) {
            $start = $sequence['start'];
        }

        $result = $this->db->manager->createSequence($sequence_name, $start);
        if (PEAR::isError($result)) {
            return $result;
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ createDatabase()

    /**
     * Create a database space within which may be created database objects
     * like tables, indexes and sequences. The implementation of this function
     * is highly DBMS specific and may require special permissions to run
     * successfully. Consult the documentation or the DBMS drivers that you
     * use to be aware of eventual configuration requirements.
     *
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function createDatabase()
    {
        if (!isset($this->database_definition['name']) || !$this->database_definition['name']) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'no valid database name specified');
        }
        $create = (isset($this->database_definition['create']) && $this->database_definition['create']);
        $overwrite = (isset($this->database_definition['overwrite']) && $this->database_definition['overwrite']);
        if ($create) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $databases = $this->db->manager->listDatabases();
            $this->db->popExpect();
            if (PEAR::isError($databases)) {
                if (!MDB2::isError($databases, $errorcodes)) {
                    return $database;
                }
            } elseif (is_array($databases) && in_array($this->database_definition['name'], $databases)) {
                if (!$overwrite) {
                    $this->db->debug('Database already exists: '.$database_name);
                    $create = false;
                } else {
                    $result = $this->db->manager->dropDatabase($this->database_definition['name']);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $this->db->debug('Overwritting database: '.$this->database_definition['name']);
                }
            }
            if ($create) {
                $result = $this->db->manager->createDatabase($this->database_definition['name']);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        $previous_database_name = $this->db->setDatabase($this->database_definition['name']);
        if (($support_transactions = $this->db->supports('transactions'))
            && PEAR::isError($result = $this->db->beginTransaction())
        ) {
            return $result;
        }

        $created_objects = 0;
        if (isset($this->database_definition['tables'])
            && is_array($this->database_definition['tables'])
        ) {
            foreach ($this->database_definition['tables'] as $table_name => $table) {
                $result = $this->createTable($table_name, $table, $overwrite);
                if (PEAR::isError($result)) {
                    break;
                }
                $created_objects++;
            }
        }
        if (!PEAR::isError($result)
            && isset($this->database_definition['sequences'])
            && is_array($this->database_definition['sequences'])
        ) {
            foreach ($this->database_definition['sequences'] as $sequence_name => $sequence) {
                $result = $this->createSequence($sequence_name, $sequence, false, $overwrite);

                if (PEAR::isError($result)) {
                    break;
                }
                $created_objects++;
            }
        }

        if (PEAR::isError($result)) {
            if ($created_objects) {
                if ($support_transactions) {
                    $res = $this->db->rollback();
                    if (PEAR::isError($res))
                        $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                            'Could not rollback the partially created database alterations ('.
                            $result->getMessage().' ('.$result->getUserinfo().'))');
                } else {
                    $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                        'the database was only partially created ('.
                        $result->getMessage().' ('.$result->getUserinfo().'))');
                }
            }
        } else {
            if ($support_transactions) {
                $res = $this->db->commit();
                if (PEAR::isError($res))
                    $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                        'Could not end transaction after successfully created the database ('.
                        $res->getMessage().' ('.$res->getUserinfo().'))');
            }
        }

        $this->db->setDatabase($previous_database_name);

        if (PEAR::isError($result) && $create
            && PEAR::isError($result2 = $this->db->manager->dropDatabase($this->database_definition['name']))
        ) {
            return $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                'Could not drop the created database after unsuccessful creation attempt ('.
                $result2->getMessage().' ('.$result2->getUserinfo().'))');
        }

        return $result;
    }

    // }}}
    // {{{ compareDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareDefinitions($previous_definition, $current_definition = null)
    {
        $current_definition = $current_definition ? $current_definition : $this->database_definition;
        $changes = array();
        if (isset($current_definition['tables']) && is_array($current_definition['tables'])) {
            $defined_tables = array();
            foreach ($current_definition['tables'] as $table_name => $table) {
                $previous_tables = array();
                if (isset($previous_definition['tables']) && is_array($previous_definition)) {
                    $previous_tables = $previous_definition['tables'];
                }
                $change = $this->compareTableDefinitions($table_name, $previous_tables, $table, $defined_tables);
                if (PEAR::isError($change)) {
                    return $change;
                }
                if (!empty($change)) {
                    $changes['tables'] = $change;
                }
            }
            if (isset($previous_definition['tables']) && is_array($previous_definition['tables'])) {
                foreach ($previous_definition['tables'] as $table_name => $table) {
                    if (!isset($defined_tables[$table_name])) {
                        $changes[$table_name]['remove'] = true;
                    }
                }
            }
        }
        if (isset($current_definition['sequences']) && is_array($current_definition['sequences'])) {
            $defined_sequences = array();
            foreach ($current_definition['sequences'] as $sequence_name => $sequence) {
                $previous_sequences = array();
                if (isset($previous_definition['sequences']) && is_array($previous_definition)) {
                    $previous_sequences = $previous_definition['sequences'];
                }
                $change = $this->compareSequenceDefinitions(
                    $sequence_name,
                    $previous_sequences,
                    $sequence,
                    $defined_sequences
                );
                if (PEAR::isError($change)) {
                    return $change;
                }
                if (!empty($change)) {
                    $changes['sequences'] = $change;
                }
            }
            if (isset($previous_definition['sequences']) && is_array($previous_definition['sequences'])) {
                foreach ($previous_definition['sequences'] as $sequence_name => $sequence) {
                    if (!isset($defined_sequences[$sequence_name])) {
                        $changes[$sequence_name]['remove'] = true;
                    }
                }
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableFieldsDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableFieldsDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_fields)
    {
        $changes = array();
        if (is_array($current_definition)) {
            foreach ($current_definition as $field_name => $field) {
                $was_field_name = $field['was'];
                if (isset($previous_definition[$field_name])
                    && isset($previous_definition[$field_name]['was'])
                    && $previous_definition[$field_name]['was'] == $was_field_name
                ) {
                    $was_field_name = $field_name;
                }
                if (isset($previous_definition[$was_field_name])) {
                    if ($was_field_name != $field_name) {
                        $declaration = $this->db->getDeclaration($field['type'], $field_name, $field);
                        if (PEAR::isError($declaration)) {
                            return $declaration;
                        }
                        $changes['renamed_fields'][$was_field_name] = array(
                            'name' => $field_name,
                            'declaration' => $declaration,
                        );
                    }
                    if (isset($defined_fields[$was_field_name])) {
                        return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                            'the field "'.$was_field_name.
                            '" was specified as base of more than one field of table');
                    }
                    $defined_fields[$was_field_name] = true;
                    $change = $this->db->compareDefinition($field, $previous_definition[$was_field_name]);
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        $declaration = $this->db->getDeclaration($field['type'], $field_name, $field);
                        if (PEAR::isError($declaration)) {
                            return $declaration;
                        }
                        $change['declaration'] = $declaration;
                        $change['definition'] = $field;
                        $changes['changed_fields'][$field_name] = $change;
                    }
                } else {
                    if ($field_name != $was_field_name) {
                        return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                            'it was specified a previous field name ("'.
                            $was_field_name.'") for field "'.$field_name.'" of table "'.
                            $table_name.'" that does not exist');
                    }
                    $declaration = $this->db->getDeclaration($field['type'], $field_name, $field);
                    if (PEAR::isError($declaration)) {
                        return $declaration;
                    }
                    $change['declaration'] = $declaration;
                    $changes['added_fields'][$field_name] = $change;
                }
            }
        }
        if (isset($previous_definition) && is_array($previous_definition)) {
            foreach ($previous_definition as $field_previous_name => $field_previous) {
                if (!isset($defined_fields[$field_previous_name])) {
                    $changes['removed_fields'][$field_previous_name] = true;
                }
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableIndexesDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableIndexesDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_indexes)
    {
        $changes = array();
        if (is_array($current_definition)) {
            foreach ($current_definition as $index_name => $index) {
                $was_index_name = $index['was'];
                if (isset($previous_definition[$index_name])
                    && isset($previous_definition[$index_name]['was'])
                    && $previous_definition[$index_name]['was'] == $was_index_name
                ) {
                    $was_index_name = $index_name;
                }
                if (isset($previous_definition[$was_index_name])) {
                    $change = array();
                    if ($was_index_name != $index_name) {
                        $change['name'] = $was_index_name;
                    }
                    if (isset($defined_indexes[$was_index_name])) {
                        return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                            'the index "'.$was_index_name.'" was specified as base of'.
                            ' more than one index of table "'.$table_name.'"');
                    }
                    $defined_indexes[$was_index_name] = true;

                    $previous_unique = isset($previous_definition[$was_index_name]['unique']);
                    $unique = isset($index['unique']);
                    if ($previous_unique != $unique) {
                        $change['changed_unique'] = true;
                        if ($unique) {
                            $change['unique'] = $unique;
                        }
                    }
                    $defined_fields = array();
                    $previous_fields = $previous_definition[$was_index_name]['fields'];
                    if (isset($index['fields']) && is_array($index['fields'])) {
                        foreach ($index['fields'] as $field_name => $field) {
                            if (isset($previous_fields[$field_name])) {
                                $defined_fields[$field_name] = true;
                                $sorting = (isset($field['sorting']) ? $field['sorting'] : '');
                                $previous_sorting = (isset($previous_fields[$field_name]['sorting'])
                                    ? $previous_fields[$field_name]['sorting'] : '');
                                if ($sorting != $previous_sorting) {
                                    $change['changed_fields'] = true;
                                }
                            } else {
                                $change['changed_fields'] = true;
                            }
                        }
                    }
                    if (isset($previous_fields) && is_array($previous_fields)) {
                        foreach ($previous_fields as $field_name => $field) {
                            if (!isset($defined_fields[$field_name])) {
                                $change['changed_fields'] = true;
                            }
                        }
                    }
                    if (!empty($change)) {
                        $changes['changed_indexes'][$index_name] = $change;
                    }
                } else {
                    if ($index_name != $was_index_name) {
                        return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                            'it was specified a previous index name ("'.$was_index_name.
                            ') for index "'.$index_name.'" of table "'.$table_name.'" that does not exist');
                    }
                    $changes['added_indexes'][$index_name] = $current_definition[$index_name];
                }
            }
        }
        foreach ($previous_definition as $index_previous_name => $index_previous) {
            if (!isset($defined_indexes[$index_previous_name])) {
                $changes['removed_indexes'][$index_previous_name] = true;
            }
        }
        return $changes;
    }

    // }}}
    // {{{ compareTableDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param string $table_name    name of the table
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareTableDefinitions($table_name, $previous_definition,
        $current_definition, &$defined_tables)
    {
        $changes = array();

        if (is_array($current_definition)) {
            $was_table_name = $table_name;
            if (isset($current_definition['was'])) {
                $was_table_name = $current_definition['was'];
            }
            if (isset($previous_definition[$was_table_name])) {
                $changes[$was_table_name] = array();
                if ($was_table_name != $table_name) {
                    $changes[$was_table_name]+= array('name' => $table_name);
                }
                if (isset($defined_tables[$was_table_name])) {
                    return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                        'the table "'.$was_table_name.
                        '" was specified as base of more than of table of the database');
                }
                $defined_tables[$was_table_name] = true;
                if (isset($current_definition['fields']) && is_array($current_definition['fields'])) {
                    $previous_fields = array();
                    if (isset($previous_definition[$was_table_name]['fields'])
                        && is_array($previous_definition[$was_table_name]['fields'])
                    ) {
                        $previous_fields = $previous_definition[$was_table_name]['fields'];
                    }
                    $defined_fields = array();
                    $change = $this->compareTableFieldsDefinitions(
                        $table_name,
                        $previous_fields,
                        $current_definition['fields'],
                        $defined_fields
                    );
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        $changes[$was_table_name]+= $change;
                    }
                }
                if (isset($current_definition['indexes']) && is_array($current_definition['indexes'])) {
                    $previous_indexes = array();
                    if (isset($previous_definition[$was_table_name]['indexes'])
                        && is_array($previous_definition[$was_table_name]['indexes'])
                    ) {
                        $previous_indexes = $previous_definition[$was_table_name]['indexes'];
                    }
                    $defined_indexes = array();
                    $change = $this->compareTableIndexesDefinitions(
                        $table_name,
                        $previous_indexes,
                        $current_definition['indexes'],
                        $defined_indexes
                    );
                    if (PEAR::isError($change)) {
                        return $change;
                    }
                    if (!empty($change)) {
                        if (isset($changes[$was_table_name]['indexes'])) {
                            $changes[$was_table_name]['indexes']+= $change;
                        } else {
                            $changes[$was_table_name]['indexes'] = $change;
                        }
                    }
                }
                if (empty($changes[$was_table_name])) {
                    unset($changes[$was_table_name]);
                }
            } else {
                if ($table_name != $was_table_name) {
                    return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                        'it was specified a previous table name ("'.
                        $was_table_name.'") for table "'.$table_name.
                        '" that does not exist');
                }
                $changes[$table_name]['add'] = true;
            }
        }

        return $changes;
    }

    // }}}
    // {{{ compareSequenceDefinitions()

    /**
     * compare a previous definition with the currenlty parsed definition
     *
     * @param array multi dimensional array that contains the previous definition
     * @param array multi dimensional array that contains the current definition
     * @return mixed array of changes on success, or a MDB2 error object
     * @access public
     */
    function compareSequenceDefinitions($sequence_name, $previous_definition,
        $current_definition, &$defined_sequences)
    {
        $changes = array();
        if (is_array($current_definition)) {
            $was_sequence_name = $sequence_name;
            if (isset($previous_definition[$sequence_name])
                && isset($previous_definition[$sequence_name]['was'])
                && $previous_definition[$sequence_name]['was'] == $was_sequence_name
            ) {
                $was_sequence_name = $sequence_name;
            } elseif (isset($current_definition['was'])) {
                $was_sequence_name = $current_definition['was'];
            }
            if (isset($previous_definition[$was_sequence_name])) {
                if ($was_sequence_name != $sequence_name) {
                    $changes[$was_sequence_name]['name'] = $sequence_name;
                }
                if (isset($defined_sequences[$was_sequence_name])) {
                    return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                        'the sequence "'.$was_sequence_name.'" was specified as base'.
                        ' of more than of sequence of the database');
                }
                $defined_sequences[$was_sequence_name] = true;
                $change = array();
                if (isset($current_definition['start'])
                    && isset($previous_definition[$was_sequence_name]['start'])
                    && $current_definition['start'] != $previous_definition[$was_sequence_name]['start']
                ) {
                    $change['start'] = $previous_definition[$sequence_name]['start'];
                }
                if (isset($current_definition['on']['table'])
                    && isset($previous_definition[$was_sequence_name]['on']['table'])
                    && $current_definition['on']['table'] != $previous_definition[$was_sequence_name]['on']['table']
                    && isset($current_definition['on']['field'])
                    && isset($previous_definition[$was_sequence_name]['on']['field'])
                    && $current_definition['on']['field'] != $previous_definition[$was_sequence_name]['on']['field']
                ) {
                    $change['on'] = $current_definition['on'];
                }
                if (!empty($change)) {
                    $changes[$was_sequence_name]['change'][$sequence_name] = $change;
                }
            } else {
                if ($sequence_name != $was_sequence_name) {
                    return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                        'it was specified a previous sequence name ("'.$was_sequence_name.
                        '") for sequence "'.$sequence_name.'" that does not exist');
                }
                $changes[$sequence_name]['add'] = true;
            }
        }
        return $changes;
    }
    // }}}
    // {{{ verifyAlterDatabase()

    /**
     * verify that the changes requested are supported
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function verifyAlterDatabase($changes)
    {
        if (isset($changes['tables']) && is_array($changes['tables'])) {
            foreach ($changes['tables'] as $table_name => $table) {
                if (isset($table['add']) || isset($table['remove'])) {
                    continue;
                }
                if (isset($table['indexes']) && is_array($table['indexes'])) {
                    if (!$this->db->supports('indexes')) {
                        return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                            'indexes are not supported');
                    }
                    foreach ($table['indexes'] as $index) {
                        $table_changes = count($index);
                        if (isset($index['add'])) {
                            $table_changes--;
                        }
                        if (isset($index['remove'])) {
                            $table_changes--;
                        }
                        if (isset($index['change'])) {
                            $table_changes--;
                        }
                        if ($table_changes) {
                            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                                'index alteration not yet supported: '.implode(', ', array_keys($index)));
                        }
                    }
                }
                $result = $this->db->manager->alterTable($table_name, $table, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        if (isset($changes['sequences']) && is_array($changes['sequences'])) {
            if (!$this->db->supports('sequences')) {
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'sequences are not supported');
            }
            foreach ($changes['sequences'] as $sequence) {
                if (isset($sequence['add']) || isset($sequence['remove']) || isset($sequence['change'])) {
                    continue;
                }
                return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'some sequences changes are not yet supported');
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ alterDatabaseIndexes()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the indexes inside a database structure.
     *
     * @param string name of the table
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseIndexes($table_name, $changes)
    {
        $alterations = 0;
        if (is_array($changes)) {
            if (isset($changes['changed_indexes'])) {
                foreach ($changes['changed_indexes'] as $index_name => $index) {
                    $result = $this->db->manager->createIndex(
                        $table_name,
                        $index_name,
                        $index
                    );
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                }
            }
            if (isset($changes['added_indexes'])) {
                foreach ($changes['added_indexes'] as $index_name => $index) {
                    $result = $this->db->manager->createIndex(
                        $table_name,
                        $index_name,
                        $index
                    );
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                }
            }
            if (isset($changes['removed_indexes'])) {
                foreach ($changes['removed_indexes'] as $index_name => $index) {
                    $result = $this->db->manager->dropIndex(
                        $table_name,
                        $index_name
                    );
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                }
            }
        }
        return $alterations;
    }

    // }}}
    // {{{ alterDatabaseTables()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the tables inside a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseTables($changes, $current_definition)
    {
        $alterations = 0;
        if (is_array($changes)) {
            foreach ($changes as $table_name => $table) {
                $indexes = null;
                if (isset($table['indexes']) && isset($current_definition[$table_name]['indexes'])) {
                    $indexes = $table['indexes'];
                    unset($table['indexes']);
                }
                if (isset($table['remove'])) {
                    $result = $this->db->manager->dropTable($table_name);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                } elseif (isset($table['add'])) {
                    $result = $this->createTable($table_name, $current_definition[$table_name]);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                } elseif(!empty($table)) {
                    $result = $this->db->manager->alterTable($table_name, $table, false);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                }
                if ($indexes) {
                    $result = $this->alterDatabaseIndexes($table_name, $indexes);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations += $result;
                }
            }
        }
        return $alterations;
    }

    // }}}
    // {{{ alterDatabaseSequences()

    /**
     * Execute the necessary actions to implement the requested changes
     * in the sequences inside a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabaseSequences($changes, $current_definition)
    {
        $alterations = 0;
        if (is_array($changes)) {
            foreach ($changes as $sequence_name => $sequence) {
                if (isset($sequence['add'])) {
                    $result = $this->createSequence($sequence_name, $sequence);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                } elseif (isset($sequence['remove'])) {
                    $result = $this->db->manager->dropSequence($sequence_name);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                } elseif (isset($sequence['change'])) {
                    $result = $this->db->manager->dropSequence($current_definition[$sequence_name]['was']);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $result = $this->createSequence($sequence_name, $current_definition[$sequence_name]);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $alterations++;
                }
            }
        }
        return $alterations;
    }

    // }}}
    // {{{ alterDatabase()

    /**
     * Execute the necessary actions to implement the requested changes
     * in a database structure.
     *
     * @param array $changes an associative array that contains the definition of
     * the changes that are meant to be applied to the database structure.
     * @param array multi dimensional array that contains the current definition
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function alterDatabase($changes, $current_definition = null)
    {
        $current_definition = $current_definition
            ? $current_definition : $this->database_definition;

        $result = $this->verifyAlterDatabase($changes);

        if (isset($current_definition['name'])) {
            $previous_database_name = $this->db->setDatabase($current_definition['name']);
        } else {
            $previous_database_name = $this->db->getDatabase();
        }
        if (($support_transactions = $this->db->supports('transactions'))
            && PEAR::isError($result = $this->db->beginTransaction())
        ) {
            return $result;
        }

        $alterations = 0;

        if (isset($changes['tables']) && isset($current_definition['tables'])) {
            $result = $this->alterDatabaseTables($changes['tables'], $current_definition['tables']);
            if (is_numeric($result)) {
                $alterations += $result;
            }
        }
        if (!PEAR::isError($result) && isset($changes['sequences']) && isset($current_definition['sequences'])) {
            $result = $this->alterDatabaseSequences($changes['sequences'], $current_definition['sequences']);
            if (is_numeric($result)) {
                $alterations += $result;
            }
        }

        if (PEAR::isError($result)) {
            if ($support_transactions) {
                $res = $this->db->rollback();
                if (PEAR::isError($res))
                    $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                        'Could not rollback the partially created database alterations ('.
                        $result->getMessage().' ('.$result->getUserinfo().'))');
            } else {
                $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                    'the requested database alterations were only partially implemented ('.
                    $result->getMessage().' ('.$result->getUserinfo().'))');
            }
        }
        if ($support_transactions) {
            $result = $this->db->commit();
            if (PEAR::isError($result)) {
                $result = $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                    'Could not end transaction after successfully implemented the requested database alterations ('.
                    $result->getMessage().' ('.$result->getUserinfo().'))');
            }
        }
        $this->db->setDatabase($previous_database_name);
        return $result;
    }

    // }}}
    // {{{ dumpDatabaseChanges()

    /**
     * Dump the changes between two database definitions.
     *
     * @param array $changes an associative array that specifies the list
     * of database definitions changes as returned by the _compareDefinitions
     * manager class function.
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function dumpDatabaseChanges($changes)
    {
        if (isset($changes['tables'])) {
            foreach ($changes['tables'] as $table_name => $table) {
                $this->db->debug("$table_name:");
                if (isset($table['add'])) {
                    $this->db->debug("\tAdded table '$table_name'");
                } elseif (isset($table['remove'])) {
                    $this->db->debug("\tRemoved table '$table_name'");
                } else {
                    if (isset($table['name'])) {
                        $this->db->debug("\tRenamed table '$table_name' to '".
                            $table['name']."'");
                    }
                    if (isset($table['added_fields'])) {
                        foreach ($table['added_fields'] as $field_name => $field) {
                            $this->db->debug("\tAdded field '".$field_name."'");
                        }
                    }
                    if (isset($table['removed_fields'])) {
                        foreach ($table['removed_fields'] as $field_name => $field) {
                            $this->db->debug("\tRemoved field '".$field_name."'");
                        }
                    }
                    if (isset($table['renamed_fields'])) {
                        foreach ($table['renamed_fields'] as $field_name => $field) {
                            $this->db->debug("\tRenamed field '".$field_name."' to '".
                                $field['name']."'");
                        }
                    }
                    if (isset($table['changed_fields'])) {
                        foreach ($table['changed_fields'] as $field_name => $field) {
                            if (isset($field['type'])) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' type to '".
                                        $field['type']."'");
                            }
                            if (isset($field['unsigned'])) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' type to '".
                                    ($field['unsigned'] ? '' : 'not ')."unsigned'");
                            }
                            if (isset($field['length'])) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' length to '".
                                    ($field['length'] == 0 ? 'no length' : $field['length'])."'");
                            }
                            if (isset($field['changed_default'])) {
                                $this->db->debug(
                                    "\tChanged field '$field_name' default to ".
                                    (isset($field['default']) ? "'".$field['default']."'" : 'NULL'));
                            }
                            if (isset($field['changed_not_null'])) {
                                $this->db->debug(
                                   "\tChanged field '$field_name' notnull to ".
                                    (isset($field['notnull']) ? "'1'" : '0')
                                );
                            }
                        }
                    }
                }
            }
        }
        if (isset($changes['sequences'])) {
            foreach ($changes['sequences'] as $sequence_name => $sequence) {
                $this->db->debug("$sequence_name:");
                if (isset($sequence['add'])) {
                    $this->db->debug("\tAdded sequence '$sequence_name'");
                } elseif (isset($sequence['remove'])) {
                    $this->db->debug("\tRemoved sequence '$sequence_name'");
                } else {
                    if (isset($sequence['name'])) {
                        $this->db->debug(
                            "\tRenamed sequence '$sequence_name' to '".
                            $sequence['name']."'");
                    }
                    if (isset($sequence['change'])) {
                        foreach ($sequence['change'] as $sequence_name => $sequence) {
                            if (isset($sequence['start'])) {
                                $this->db->debug(
                                    "\tChanged sequence '$sequence_name' start to '".
                                    $sequence['start']."'");
                            }
                        }
                    }
                }
            }
        }
        if (isset($changes['indexes'])) {
            foreach ($changes['indexes'] as $table_name => $table) {
                $this->db->debug("$table_name:");
                if (isset($table['added_indexes'])) {
                    foreach ($table['added_indexes'] as $index_name => $index) {
                        $this->db->debug("\tAdded index '".$index_name.
                            "' of table '$table_name'");
                    }
                }
                if (isset($table['removed_indexes'])) {
                    foreach ($table['removed_indexes'] as $index_name => $index) {
                        $this->db->debug("\tRemoved index '".$index_name.
                            "' of table '$table_name'");
                    }
                }
                if (isset($table['changed_indexes'])) {
                    foreach ($table['changed_indexes'] as $index_name => $index) {
                        if (isset($index['name'])) {
                            $this->db->debug(
                                "\tRenamed index '".$index_name."' to '".$index['name'].
                                "' on table '$table_name'");
                        }
                        if (isset($index['changed_unique'])) {
                            $this->db->debug(
                                "\tChanged index '".$index_name."' unique to '".
                                isset($index['unique'])."' on table '$table_name'");
                        }
                        if (isset($index['changed_fields'])) {
                            $this->db->debug("\tChanged index '".$index_name.
                                "' on table '$table_name'");
                        }
                    }
                }
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ dumpDatabase()

    /**
     * Dump a previously parsed database structure in the Metabase schema
     * XML based format suitable for the Metabase parser. This function
     * may optionally dump the database definition with initialization
     * commands that specify the data that is currently present in the tables.
     *
     * @param array $arguments an associative array that takes pairs of tag
     * names and values that define dump options.
     *                 array (
     *                     'definition'    =>    Boolean
     *                         true   :  dump currently parsed definition
     *                         default:  dump currently connected database
     *                     'output_mode'    =>    String
     *                         'file' :   dump into a file
     *                         default:   dump using a function
     *                     'output'        =>    String
     *                         depending on the 'Output_Mode'
     *                                  name of the file
     *                                  name of the function
     *                     'end_of_line'        =>    String
     *                         end of line delimiter that should be used
     *                         default: "\n"
     *                 );
     * @param integer $dump constant that determines what data to dump
     *                      MDB2_MANAGER_DUMP_ALL       : the entire db
     *                      MDB2_MANAGER_DUMP_STRUCTURE : only the structure of the db
     *                      MDB2_MANAGER_DUMP_CONTENT   : only the content of the db
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function dumpDatabase($arguments, $dump = MDB2_MANAGER_DUMP_ALL)
    {
        if (!isset($arguments['definition']) || !$arguments['definition']) {
            if (!$this->db) {
                return $this->raiseError(MDB2_ERROR_NODBSELECTED,
                    null, null, 'please connect to a RDBMS first');
            }
            $error = $this->getDefinitionFromDatabase();
            if (PEAR::isError($error)) {
                return $error;
            }

            // get initialization data
            if (isset($this->database_definition['tables']) && is_array($this->database_definition['tables'])
                && $dump == MDB2_MANAGER_DUMP_ALL || $dump == MDB2_MANAGER_DUMP_CONTENT
            ) {
                foreach ($this->database_definition['tables'] as $table_name => $table) {
                    $fields = array();
                    $types = array();
                    foreach ($table['fields'] as $field_name => $field) {
                        $fields[$field_name] = $field['type'];
                    }
                    $query = 'SELECT '.implode(', ', array_keys($fields)).' FROM '.$table_name;
                    $data = $this->db->queryAll($query, $types, MDB2_FETCHMODE_ASSOC);
                    if (PEAR::isError($data)) {
                        return $data;
                    }
                    if (!empty($data)) {
                        $initialization = array();
                        foreach ($data as $row) {
                            foreach($row as $key => $lob) {
                                if (is_numeric($lob) && isset($fields[$key])
                                    && ($fields[$key] == 'clob' || $fields[$key] == 'blob')
                                ) {
                                    $value = '';
                                    while (!$this->db->datatype->endOfLOB($lob)) {
                                        $this->db->datatype->readLOB($lob, $data, 8192);
                                        $value .= $data;
                                    }
                                    $row[$key] = $value;
                                }
                            }
                            $initialization[] = array('type' => 'insert', 'fields' => $row);
                        }
                        $this->database_definition['tables'][$table_name]['initialization'] = $initialization;
                    }
                }
            }
        }

        require_once 'MDB2/Schema/Writer.php';
        $writer =& new MDB2_Schema_Writer();
        return $writer->dumpDatabase($this->database_definition, $arguments, $dump);
    }

    // }}}
    // {{{ writeInitialization()

    /**
     * write initialization and sequences
     *
     * @param string $data_file
     * @param string $structure_file
     * @param array $variables an associative array that is passed to the argument
     * of the same name to the parseDatabaseDefinitionFile function. (there third
     * param)
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function writeInitialization($data, $structure = false, $variables = array())
    {
        if ($structure) {
            $structure = $this->parseDatabaseDefinitionFile(
                $structure_file,
                $variables
            );
            if (PEAR::isError($structure)) {
                return $structure;
            }
        }

        $data = $this->parseDatabaseDefinitionFile(
            $data_file,
            $variables,
            false,
            $structure
        );
        if (PEAR::isError($data)) {
            return $data;
        }

        $previous_database_name = null;
        if (isset($data['name'])) {
            $previous_database_name = $this->db->setDatabase($data['name']);
        } elseif(isset($structure['name'])) {
            $previous_database_name = $this->db->setDatabase($structure['name']);
        }

        if (isset($data['tables']) && is_array($data['tables'])) {
            foreach ($data['tables'] as $table_name => $table) {
                if (!isset($table['initialization'])) {
                    continue;
                }
                $table['fields'] = $structure['tables'][$table_name]['fields'];
                $result = $this->initializeTable($table_name, $table);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        if (isset($structure['sequences']) && is_array($structure['sequences'])) {
            foreach ($structure['sequences'] as $sequence_name => $sequence) {
                if (isset($data['sequences'][$sequence_name])
                    || !isset($sequence['on']['table'])
                    || !isset($data['tables'][$sequence['on']['table']])
                ) {
                    continue;
                }
                $result = $this->createSequence($sequence_name, $sequence, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }
        if (isset($data['sequences']) && is_array($data['sequences'])) {
            foreach ($data['sequences'] as $sequence_name => $sequence) {
                $result = $this->createSequence($sequence_name, $sequence, true);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
        }

        if (isset($previous_database_name)) {
            $this->db->setDatabase($previous_database_name);
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ updateDatabase()

    /**
     * Compare the correspondent files of two versions of a database schema
     * definition: the previously installed and the one that defines the schema
     * that is meant to update the database.
     * If the specified previous definition file does not exist, this function
     * will create the database from the definition specified in the current
     * schema file.
     * If both files exist, the function assumes that the database was previously
     * installed based on the previous schema file and will update it by just
     * applying the changes.
     * If this function succeeds, the contents of the current schema file are
     * copied to replace the previous schema file contents. Any subsequent schema
     * changes should only be done on the file specified by the $current_schema_file
     * to let this function make a consistent evaluation of the exact changes that
     * need to be applied.
     *
     * @param string $current_schema_file name of the updated database schema
     * definition file.
     * @param string $previous_schema_file name the previously installed database
     * schema definition file.
     * @param array $variables an associative array that is passed to the argument
     * of the same name to the parseDatabaseDefinitionFile function. (there third
     * param)
     * @return mixed MDB2_OK on success, or a MDB2 error object
     * @access public
     */
    function updateDatabase($current_schema_file, $previous_schema_file = false, $variables = array())
    {
        $database_definition = $this->parseDatabaseDefinitionFile(
            $current_schema_file,
            $variables,
            $this->options['fail_on_invalid_names']
        );

        if (PEAR::isError($database_definition)) {
            return $database_definition;
        }

        $this->database_definition = $database_definition;
        if ($previous_schema_file && file_exists($previous_schema_file)) {
            $errorcodes = array(MDB2_ERROR_UNSUPPORTED, MDB2_ERROR_NOT_CAPABLE);
            $this->db->expectError($errorcodes);
            $databases = $this->db->manager->listDatabases();
            $this->db->popExpect();
            if (PEAR::isError($databases)) {
                if (!MDB2::isError($databases, $errorcodes)) {
                    return $databases;
                }
            } elseif (!is_array($databases) ||
                !in_array($this->database_definition['name'], $databases)
            ) {
                return $this->raiseError(MDB2_ERROR, null, null,
                    'database to update does not exist: '.$this->database_definition['name']);
            }
            $previous_definition = $this->parseDatabaseDefinitionFile($previous_schema_file, $variables, 0);
            if (PEAR::isError($previous_definition)) {
                return $previous_definition;
            }
            $changes = $this->compareDefinitions($previous_definition);
            if (PEAR::isError($changes)) {
                return $changes;
            }
            if (is_array($changes)) {
                $result = $this->alterDatabase($changes, $previous_definition);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $copy = true;
                if ($this->db->options['debug']) {
                    $result = $this->dumpDatabaseChanges($changes);
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                }
            }
        } else {
            $result = $this->createDatabase();
            if (PEAR::isError($result)) {
                return $result;
            }
        }
        if ($previous_schema_file && !copy($current_schema_file, $previous_schema_file)) {
            return $this->raiseError(MDB2_ERROR_MANAGER, null, null,
                'Could not copy the new database definition file to the current file');
        }
        return MDB2_OK;
    }

    // }}}
}
?>