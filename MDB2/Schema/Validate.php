<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
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
// | Author: Christian Dickmann <dickmann@php.net>                        |
// | Author: Igor Feghali <ifeghali@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * Validates an XML schema file
 *
 * @package MDB2_Schema
 * @category Database
 * @access protected
 * @author Igor Feghali <ifeghali@php.net>
 */
class MDB2_Schema_Validate
{
    var $fail_on_invalid_names = true;
    var $valid_types = array();
    var $force_defaults = true;

    function __construct($fail_on_invalid_names = true, $valid_types = array(), $force_defaults = true)
    {
        if (is_array($fail_on_invalid_names)) {
            $this->fail_on_invalid_names
                = array_intersect($fail_on_invalid_names, array_keys($GLOBALS['_MDB2_Schema_Reserved']));
        } elseif ($this->fail_on_invalid_names === true) {
            $this->fail_on_invalid_names = array_keys($GLOBALS['_MDB2_Schema_Reserved']);
        } else {
            $this->fail_on_invalid_names = false;
        }
        $this->valid_types = $valid_types;
        $this->force_defaults = $force_defaults;
    }

    function &raiseError($ecode, $msg = null)
    {
        $error =& MDB2_Schema::raiseError($ecode, null, null, $msg);
        return $error;
    }

    function isBoolean(&$value)
    {
        if (is_bool($value)) {
            return true;
        }
        if ($value === 0 || $value === 1) {
            $value = (bool)$value;
            return true;
        }
        if (!is_string($value)) {
            return false;
        }
        switch ($value) {
        case '0':
        case 'N':
        case 'n':
        case 'no':
        case 'false':
            $value = false;
            break;
        case '1':
        case 'Y':
        case 'y':
        case 'yes':
        case 'true':
            $value = true;
            break;
        default:
            return false;
        }
        return true;
    }

    /* Definition */
    function validateTable(&$tables, &$table, $table_name)
    {
        if (!$table_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_TABLE_NAME,
                'a table has to have a name');
        } elseif (is_array($this->fail_on_invalid_names) && !empty($this->fail_on_invalid_names)) {
            $name = strtoupper($table_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_TABLE_NAME,
                        'table name "'.$table_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }

        if (isset($tables[$table_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_TABLE_EXISTS,
                'table "'.$table_name.'" already exists');
        }

        if (empty($table['was'])) {
            $table['was'] = $table_name;
        }

        $autoinc = $primary = false;
        if (empty($table['fields']) || !is_array($table['fields'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_FIELDS,
                'tables need one or more fields');
        } else {
            foreach ($table['fields'] as $field_name => $field) {
                if (!empty($field['autoincrement'])) {
                    if ($primary) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DUP_AUTOINC,
                            'there was already an autoincrement field in "'.$table_name.'" before "'.$field_name.'"');
                    } else {
                        $autoinc = $primary = true;
                    }

                    if (!$table['fields'][$field_name]['notnull']) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_AUTOINC_NULL,
                            'all autoincrement fields must be defined notnull in "'.$table_name.'"');
                    }

                    if (empty($field['default'])) {
                        $table['fields'][$field_name]['default'] = '0';
                    } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_AUTOINC_INVALID_DEFAULT,
                            'all autoincrement fields must be defined default "0" in "'.$table_name.'"');
                    }
                }
            }
        }
        if (!empty($table['indexes']) && is_array($table['indexes'])) {
            foreach ($table['indexes'] as $name => $index) {
                $skip_index = false;
                if (!empty($index['primary'])) {
                    /*
                        * Lets see if we should skip this index since there is
                        * already a auto increment on this field this implying
                        * a primary key index.
                        */
                    if ($autoinc && count($index['fields']) == '1') {
                        $skip_index = true;
                    } elseif ($primary) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_PRIMARY_EXISTS,
                            'there was already an primary index or autoincrement field in "'.$table_name.'" before "'.$name.'"');
                    } else {
                        $primary = true;
                    }
                }

                if (!$skip_index && is_array($index['fields'])) {
                    foreach ($index['fields'] as $field_name => $field) {
                        if (!isset($table['fields'][$field_name])) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_BROKEN_INDEX,
                                'index field "'.$field_name.'" does not exist');
                        } elseif (!empty($index['primary'])
                            && !$table['fields'][$field_name]['notnull']
                        ) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_PRIMARY_NULL,
                                'all primary key fields must be defined notnull in "'.$table_name.'"');
                        }
                    }
                } else {
                    unset($table['indexes'][$name]);
                }
            }
        }
        $tables[$table_name] = $table;
        return true;
    }

    function validateField(&$fields, &$field, $field_name)
    {
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_FIELD_NAME,
                'field name missing');
        } elseif (isset($fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_FIELD_EXISTS,
                'field "'.$field_name.'" already exists');
        }

        if (is_array($this->fail_on_invalid_names) && !empty($this->fail_on_invalid_names))
            $name = strtoupper($field_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_FIELD_NAME,
                        'field name "'.$field_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }
        /* Type check */
        if (empty($field['type'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_FIELD_TYPE,
                'no field type specified');
        }
        if (!empty($this->valid_types) && !array_key_exists($field['type'], $this->valid_types)) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_FIELD_TYPE,
                'no valid field type ("'.$field['type'].'") specified');
        }
        if (array_key_exists('unsigned', $field) && !$this->isBoolean($field['unsigned'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_UNSIGNED,
                'unsigned has to be a boolean value');
        }
        if (array_key_exists('fixed', $field) && !$this->isBoolean($field['fixed'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_FIXED,
                'fixed has to be a boolean value');
        }
        if (array_key_exists('length', $field) && $field['length'] <= 0) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_LENGTH,
                'length has to be an integer greater 0');
        }
        if (empty($field['was'])) {
            $field['was'] = $field_name;
        }
        if (empty($field['notnull'])) {
            $field['notnull'] = false;
        }
        if (!$this->isBoolean($field['notnull'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_NOTNULL,
                'field "notnull" has to be a boolean value');
        }
        if ($this->force_defaults
            && !array_key_exists('default', $field)
            && $field['type'] != 'clob' && $field['type'] != 'blob'
        ) {
            $field['default'] = $this->valid_types[$field['type']];
        }

        if (array_key_exists('default', $field)) {
            if ($field['type'] == 'clob' || $field['type'] == 'blob') {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DEFAULT_NOT_ALLOWED,
                    '"'.$field['type'].'"-fields are not allowed to have a default value');
            }
            if ($field['default'] === '') {
                if (!$field['notnull']) {
                    $field['default'] = null;
                }
            }
        }

        $fields[$field_name] = $field;

        if (isset($field['default'])
            && !$this->validateFieldValue($fields, $field_name, $fields[$field_name]['default'])
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_DEFAULT,
                'default value of "'.$field_name.'" is of wrong type');
        }
        return true;
    }

    function validateIndex(&$table_indexes, &$index, $index_name)
    {
        if (!$index_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_INDEX_NAME,
                'an index has to have a name');
        }
        if (isset($table_indexes[$index_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INDEX_EXISTS,
                'index "'.$index_name.'" already exists');
        }
        if (array_key_exists('unique', $index) && !$this->isBoolean($index['unique'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_UNIQUE,
                'field "unique" has to be a boolean value');
        }
        if (array_key_exists('primary', $index) && !$this->isBoolean($index['primary'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_PRIMARY,
                'field "primary" has to be a boolean value');
        }

        if (empty($index['was'])) {
            $index['was'] = $index_name;
        }
        $table_indexes[$index_name] = $index;
        return true;
    }

    function validateIndexField(&$index_fields, &$field, $field_name)
    {
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_INDEX_FIELD_NAME,
                'the index-field-name is required');
        }
        if (!empty($field['sorting'])
            && $field['sorting'] !== 'ascending' && $field['sorting'] !== 'descending') {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_SORT,
                'sorting type unknown');
        } else {
            $field['sorting'] = 'ascending';
        }
        $index['fields'][$field_name] = $field;
        return true;
    }

    function validateTableName(&$table, $table_name, $structure_tables)
    {

        return true;
    }

    function validateSequence(&$sequences, &$sequence, $sequence_name)
    {
        if (!$sequence_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_SEQUENCE_NAME,
                'a sequence has to have a name');
        } elseif (is_array($this->fail_on_invalid_names) && !empty($this->fail_on_invalid_names))
            $name = strtoupper($sequence_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_SEQUENCE_NAME,
                        'sequence name "'.$sequence_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }

        if (isset($sequences[$sequence_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_SEQUENCE_EXISTS,
                'sequence "'.$sequence_name.'" already exists');
        }

        if (empty($sequence['was'])) {
            $sequence['was'] = $sequence_name;
        }

        if (!empty($sequence['on'])) {
            if (empty($sequence['on']['table']) || empty($sequence['on']['field'])) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_SEQUENCE,
                    'sequence "'.$sequence_name.'" was not properly defined');
            }
        }
        $sequences[$sequence_name] = $sequence;
        return true;
    }

    function validateDatabase(&$database)
    {
        if (!isset($database['name']) || !$database['name']) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_NO_DATABASE_NAME,
                'a database has to have a name');
        } elseif (is_array($this->fail_on_invalid_names) && !empty($this->fail_on_invalid_names))
            $name = strtoupper($database['name']);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_DATABASE_NAME,
                        'database name "'.$database['name'].'" is a reserved word in: '.$rdbms);
                }
            }
        }

        if (isset($database['create'])
            && !$this->isBoolean($database['create'])
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_CREATE,
                'field "create" has to be a boolean value');
        }
        if (isset($database['overwrite'])
            && !$this->isBoolean($database['overwrite'])
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_INVALID_OVERWRITE,
                'field "overwrite" has to be a boolean value');
        }

        if (isset($database['sequences'])) {
            foreach ($database['sequences'] as $seq_name => $seq) {
                if (!empty($seq['on'])
                    && empty($database['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                ) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_BROKEN_SEQUENCE,
                        'sequence "'.$seq_name.'" was assigned on unexisting field/table');
                }
            }
        }
        return true;
    }

    /* Data Manipulation */
    function validateInsertField(&$table_fields, &$instruction, $field_name, $value)
    {
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_NO_FIELD_NAME,
                'field-name has to be specified');
        }
        if (isset($instruction['fields'][$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_FIELD_EXISTS,
                'field "'.$field_name.'" already filled');
        }
        if (!isset($table_fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_FIELD_NOT_FOUND,
                'unknown field "'.$field_name.'"');
        }
        if ($value !== ''
            && !$this->validateFieldValue($table_fields, $field_name, $value)
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_FIELD_VALUE,
                'field "'.$field_name.'" has wrong value');
        }
        $instruction['fields'][$field_name] = $value;
        return true;
    }

    function validateDML(&$table, &$instruction)
    {
        $table['initialization'][] = $instruction;
        return true;
    }

    function validateFieldValue($fields, $field_name, &$field_value)
    {
        if (!isset($fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_FIELD_NOT_FOUND,
                '"'.$field_name.'" is not defined');
        }
        $field_def = $fields[$field_name];
        switch ($field_def['type']) {
        case 'text':
        case 'clob':
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_VALUE_TOO_BIG,
                    '"'.$field_value.'" is larger than "'.$field_def['length'].'"');
            }
            break;
        case 'blob':
            /*
            if (!preg_match('/^([0-9a-f]{2})*$/i', $field_value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            */
            $field_value = pack('H*', $field_value);
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_VALUE_TOO_BIG,
                    '"'.$field_value.'" is larger than "'.$field_def['type'].'"');
            }
            break;
        case 'integer':
            if ($field_value != ((int)$field_value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            $field_value = (int) $field_value;
            if (!empty($field_def['unsigned']) && $field_def['unsigned'] && $field_value < 0) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_NEGATIVE_VALUE_FOR_SIGNED_FIELD,
                    '"'.$field_value.'" signed instead of unsigned');
            }
            break;
        case 'boolean':
            if (!$this->isBoolean($field_value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'date':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $field_value)
                && $field_value !== 'CURRENT_DATE'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'timestamp':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $field_value)
                && $field_value !== 'CURRENT_TIMESTAMP'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'time':
            if (!preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $field_value)
                && $field_value !== 'CURRENT_TIME'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'float':
        case 'double':
            if ($field_value != (double)$field_value) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE_DML_INVALID_VALUE_TYPE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            $field_value = (double) $field_value;
            break;
        }
        return true;
    }
}

?>
