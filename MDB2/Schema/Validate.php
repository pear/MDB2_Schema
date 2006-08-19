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
 * The method mapErrorCode in each MDB2_Schema_dbtype implementation maps
 * native error codes to one of these.
 *
 * If you add an error code here, make sure you also add a textual
 * version of it in MDB2_Schema::errorMessage().
 */

define('MDB2_SCHEMA_VALIDATE_AUTOINC_INVALID_DEFAULT',              -1);
define('MDB2_SCHEMA_VALIDATE_AUTOINC_NULL',                         -2);
define('MDB2_SCHEMA_VALIDATE_BROKEN_INDEX',                         -3);
define('MDB2_SCHEMA_VALIDATE_BROKEN_SEQUENCE',                      -4);
define('MDB2_SCHEMA_VALIDATE_DEFAULT_NOT_ALLOWED',                  -5);
define('MDB2_SCHEMA_VALIDATE_DML_FIELD_EXISTS',                     -6);
define('MDB2_SCHEMA_VALIDATE_DML_FIELD_NOT_FOUND',                  -7);
define('MDB2_SCHEMA_VALIDATE_DML_INVALID_FIELD_VALUE',              -8);
define('MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE',               -9);
define('MDB2_SCHEMA_VALIDATE_DML_NEGATIVE_VALUE_FOR_SIGNED_FIELD',  -10);
define('MDB2_SCHEMA_VALIDATE_DML_NO_FIELD_NAME',                    -11);
define('MDB2_SCHEMA_VALIDATE_DML_VALUE_TOO_BIG',                    -12);
define('MDB2_SCHEMA_VALIDATE_DUP_AUTOINC',                          -13);
define('MDB2_SCHEMA_VALIDATE_FIELD_EXISTS',                         -14);
define('MDB2_SCHEMA_VALIDATE_INDEX_EXISTS',                         -15);
define('MDB2_SCHEMA_VALIDATE_INVALID_CREATE',                       -16);
define('MDB2_SCHEMA_VALIDATE_INVALID_DATABASE_NAME',                -17);
define('MDB2_SCHEMA_VALIDATE_INVALID_DEFAULT',                      -18);
define('MDB2_SCHEMA_VALIDATE_INVALID_FIELD_NAME',                   -19);
define('MDB2_SCHEMA_VALIDATE_INVALID_FIELD_TYPE',                   -20);
define('MDB2_SCHEMA_VALIDATE_INVALID_FIXED',                        -21);
define('MDB2_SCHEMA_VALIDATE_INVALID_LENGTH',                       -22);
define('MDB2_SCHEMA_VALIDATE_INVALID_NOTNULL',                      -23);
define('MDB2_SCHEMA_VALIDATE_INVALID_OVERWRITE',                    -24);
define('MDB2_SCHEMA_VALIDATE_INVALID_PRIMARY',                      -25);
define('MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE',                     -26);
define('MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE_NAME',                -27);
define('MDB2_SCHEMA_VALIDATE_INVALID_SORT',                         -28);
define('MDB2_SCHEMA_VALIDATE_INVALID_TABLE_NAME',                   -29);
define('MDB2_SCHEMA_VALIDATE_INVALID_UNIQUE',                       -30);
define('MDB2_SCHEMA_VALIDATE_INVALID_UNSIGNED',                     -31);
define('MDB2_SCHEMA_VALIDATE_NO_DATABASE_NAME',                     -32);
define('MDB2_SCHEMA_VALIDATE_NO_FIELD_NAME',                        -33);
define('MDB2_SCHEMA_VALIDATE_NO_FIELD_TYPE',                        -34);
define('MDB2_SCHEMA_VALIDATE_NO_FIELDS',                            -35);
define('MDB2_SCHEMA_VALIDATE_NO_INDEX_FIELD_NAME',                  -36);
define('MDB2_SCHEMA_VALIDATE_NO_INDEX_NAME',                        -37);
define('MDB2_SCHEMA_VALIDATE_NO_SEQUENCE_NAME',                     -38);
define('MDB2_SCHEMA_VALIDATE_NO_TABLE_NAME',                        -39);
define('MDB2_SCHEMA_VALIDATE_PRIMARY_EXISTS',                       -40);
define('MDB2_SCHEMA_VALIDATE_PRIMARY_NULL',                         -41);
define('MDB2_SCHEMA_VALIDATE_SEQUENCE_EXISTS',                      -42);
define('MDB2_SCHEMA_VALIDATE_TABLE_EXISTS',                         -43);

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
    function validateTable(&$tables, &$table, $table_name, $fail_on_invalid_names = array(), $xp)
    {
        if (!$table_name) {
            //schem.raiseError('a table has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_TABLE_NAME;
        } elseif ($fail_on_invalid_names) {
            $name = strtoupper($table_name);
            foreach ($fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return schem.raiseError('table name "'.$table_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_TABLE_NAME;
                }
            }
        }

        if (isset($tables[$table_name])) {
            //schem.raiseError('table "'.$table_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_TABLE_EXISTS;
        }

        if (empty($table['was'])) {
            $table['was'] = $table_name;
        }

        $autoinc = $primary = false;
        if (empty($table['fields']) || !is_array($table['fields'])) {
            //schem.raiseError('tables need one or more fields', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELDS;
        } else {
            foreach ($table['fields'] as $field_name => $field) {
                if (!empty($field['autoincrement'])) {
                    if ($primary) {
                        //schem.raiseError('there was already an autoincrement field in "'.$table_name.'" before "'.$field_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_DUP_AUTOINC;
                    } else {
                        $autoinc = $primary = true;
                    }

                    if (!$table['fields'][$field_name]['notnull']) {
                        //schem.raiseError('all autoincrement fields must be defined notnull in "'.$table_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_AUTOINC_NULL;
                    }

                    if (empty($field['default'])) {
                        $table['fields'][$field_name]['default'] = '0';
                    } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                        //schem.raiseError('all autoincrement fields must be defined default "0" in "'.$table_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_AUTOINC_INVALID_DEFAULT;
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
                        //schem.raiseError('there was already an primary index or autoincrement field in "'.$table_name.'" before "'.$name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_PRIMARY_EXISTS;
                    } else {
                        $primary = true;
                    }
                }

                if (!$skip_index && is_array($index['fields'])) {
                    foreach ($index['fields'] as $field_name => $field) {
                        if (!isset($table['fields'][$field_name])) {
                            //schem.raiseError('index field "'.$field_name.'" does not exist', null, $xp);
                            return MDB2_SCHEMA_VALIDATE_BROKEN_INDEX;
                        } elseif (!empty($index['primary'])
                            && !$table['fields'][$field_name]['notnull']
                        ) {
                            //schem.raiseError('all primary key fields must be defined notnull in "'.$table_name.'"', null, $xp);
                            return MDB2_SCHEMA_VALIDATE_PRIMARY_NULL;
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

    function validateField(&$fields, &$field, $field_name, $force_defaults, $valid_types = array(), $fail_on_invalid_names = array(), $xp)
    {
        if (!$field_name) {
            //schem.raiseError('field name missing', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELD_NAME;
        } elseif (isset($fields[$field_name])) {
            //schem.raiseError('field "'.$field_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_FIELD_EXISTS;
        }

        if ($fail_on_invalid_names) {
            $name = strtoupper($field_name);
            foreach ($fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return schem.raiseError('field name "'.$field_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_FIELD_NAME;
                }
            }
        }
        /* Type check */
        if (empty($field['type'])) {
            //return schem.raiseError('no field type specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELD_TYPE;
        }
        if (!empty($valid_types) && !array_key_exists($field['type'], $valid_types)) {
            //schem.raiseError('no valid field type ("'.$field['type'].'") specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_FIELD_TYPE;
        }
        if (array_key_exists('unsigned', $field) && !$this->isBoolean($field['unsigned'])) {
            //schem.raiseError('unsigned has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_UNSIGNED;
        }
        if (array_key_exists('fixed', $field) && !$this->isBoolean($field['fixed'])) {
            //schem.raiseError('fixed has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_FIXED;
        }
        if (array_key_exists('length', $field) && $field['length'] <= 0) {
            //schem.raiseError('length has to be an integer greater 0', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_LENGTH;
        }
        if (empty($field['was'])) {
            $field['was'] = $field_name;
        }
        if (empty($field['notnull'])) {
            $field['notnull'] = false;
        }
        if (!$this->isBoolean($field['notnull'])) {
            //schem.raiseError('field "notnull" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_NOTNULL;
        }
        if ($force_defaults
            && !array_key_exists('default', $field)
            && $field['type'] != 'clob' && $field['type'] != 'blob'
        ) {
            $field['default'] = $valid_types[$field['type']];
        }

        if (array_key_exists('default', $field)) {
            if ($field['type'] == 'clob' || $field['type'] == 'blob') {
                /*schem.raiseError('"'.$field['type'].
                    '"-fields are not allowed to have a default value', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DEFAULT_NOT_ALLOWED;
            }
            if ($field['default'] === '') {
                if (!$field['notnull']) {
                    $field['default'] = null;
                }
            }
        }

        $fields[$field_name] = $field;

        if (isset($field['default'])
            && !$this->validateFieldValue($fields, $field_name,
                $fields[$field_name]['default'], $xp
            )
        ) {
            //schem.raiseError('default value of "'.$field_name.'" is of wrong type', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_DEFAULT;
        }
        return true;
    }

    function validateIndex(&$table_indexes, &$index, $index_name, $xp)
    {
        if (!$index_name) {
            //schem.raiseError('an index has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_INDEX_NAME;
        }
        if (isset($table_indexes[$index_name])) {
            //schem.raiseError('index "'.$index_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INDEX_EXISTS;
        }
        if (array_key_exists('unique', $index) && !$this->isBoolean($index['unique'])) {
            //schem.raiseError('field "unique" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_UNIQUE;
        }
        if (array_key_exists('primary', $index) && !$this->isBoolean($index['primary'])) {
            //schem.raiseError('field "primary" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_PRIMARY;
        }

        if (empty($index['was'])) {
            $index['was'] = $index_name;
        }
        $table_indexes[$index_name] = $index;
        return true;
    }

    function validateIndexField(&$index_fields, &$field, $field_name, $xp)
    {
        if (!$field_name) {
            //schem.raiseError('the index-field-name is required', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_INDEX_FIELD_NAME;
        }
        if (!empty($field['sorting'])
            && $field['sorting'] !== 'ascending' && $field['sorting'] !== 'descending') {
            //schem.raiseError('sorting type unknown', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_SORT;
        } else {
            $field['sorting'] = 'ascending';
        }
        $index['fields'][$field_name] = $field;
        return true;
    }

    function validateTableName(&$table, $table_name, $structure_tables, $xp)
    {
        if (isset($structure_tables[$table_name])) {
            $table = $structure_tables[$table_name];
        }
        return true;
    }

    function validateSequence(&$sequences, &$sequence, $sequence_name, $fail_on_invalid_names = array(), $xp)
    {
        if (!$sequence_name) {
            //schem.raiseError('a sequence has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_SEQUENCE_NAME;
        } elseif ($fail_on_invalid_names) {
            $name = strtoupper($sequence_name);
            foreach ($fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return schem.raiseError('sequence name "'.$sequence_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE_NAME;
                }
            }
        }

        if (isset($sequences[$sequence_name])) {
            //schem.raiseError('sequence "'.$sequence_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_SEQUENCE_EXISTS;
        }

        if (empty($sequence['was'])) {
            $sequence['was'] = $sequence_name;
        }

        if (!empty($sequence['on'])) {
            if (empty($sequence['on']['table']) || empty($sequence['on']['field'])) {
                /*schem.raiseError('sequence "'.$sequence_name.
                    '" was not properly defined', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE;
            }
        }
        $sequences[$sequence_name] = $sequence;
        return true;
    }

    function validateDatabase(&$database, $error, $fail_on_invalid_names = array(), $xp)
    {
        if (!isset($database['name']) || !$database['name']) {
            //schem.raiseError('a database has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_DATABASE_NAME;
        } elseif ($fail_on_invalid_names) {
            $name = strtoupper($database['name']);
            foreach ($fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return schem.raiseError('database name "'.$database['name'].'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_DATABASE_NAME;
                }
            }
        }

        if (isset($database['create'])
            && !$this->isBoolean($database['create'])
        ) {
            //schem.raiseError('field "create" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_CREATE;
        }
        if (isset($database['overwrite'])
            && !$this->isBoolean($database['overwrite'])
        ) {
            //schem.raiseError('field "overwrite" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_OVERWRITE;
        }

        if (isset($database['sequences'])) {
            foreach ($database['sequences'] as $seq_name => $seq) {
                if (!empty($seq['on'])
                    && empty($database['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                ) {
                    /*schem.raiseError('sequence "'.$seq_name.
                        '" was assigned on unexisting field/table', null, $xp);*/
                    return MDB2_SCHEMA_VALIDATE_BROKEN_SEQUENCE;
                }
            }
        }
        if (PEAR::isError($error)) {
            $database = $error;
        }
        return true;
    }

    /* Data Manipulation */
    function validateInsertField(&$table_fields, &$instruction, $field_name, $value, $xp)
    {
        if (!$field_name) {
            //schem.raiseError('field-name has to be specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_NO_FIELD_NAME;
        }
        if (isset($instruction['fields'][$field_name])) {
            //schem.raiseError('field "'.$field_name.'" already filled', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_EXISTS;
        }
        if (!isset($table_fields[$field_name])) {
            //schem.raiseError('unknown field "'.$field_name.'"', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_NOT_FOUND;
        }
        if ($value !== ''
            && !$this->validateFieldValue($table_fields, $field_name, $value, $xp)
        ) {
            //schem.raiseError('field "'.$field_name.'" has wrong value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_INVALID_FIELD_VALUE;
        }
        $instruction['fields'][$field_name] = $value;
        return true;
    }

    function validateDML(&$table, &$instruction, $xp)
    {
        $table['initialization'][] = $instruction;
        return true;
    }

    function validateFieldValue($fields, $field_name, &$field_value, $xp)
    {
        if (!isset($fields[$field_name])) {
            //return schem.raiseError('"'.$field_name.'" is not defined', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_NOT_FOUND;
        }
        $field_def = $fields[$field_name];
        switch ($field_def['type']) {
        case 'text':
        case 'clob':
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                /*return schem.raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['length'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_VALUE_TOO_BIG;
            }
            break;
        case 'blob':
            /*
            if (!preg_match('/^([0-9a-f]{2})*$/i', $field_value)) {
                return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            */
            $field_value = pack('H*', $field_value);
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                /*return schem.raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_VALUE_TOO_BIG;
            }
            break;
        case 'integer':
            if ($field_value != ((int)$field_value)) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            $field_value = (int) $field_value;
            if (!empty($field_def['unsigned']) && $field_def['unsigned'] && $field_value < 0) {
                //return schem.raiseError('"'.$field_value.'" signed instead of unsigned', null, $xp);
                return MDB2_SCHEMA_VALIDATE_DML_NEGATIVE_VALUE_FOR_SIGNED_FIELD;
            }
            break;
        case 'boolean':
            if (!$this->isBoolean($field_value)) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'date':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $field_value)
                && $field_value !== 'CURRENT_DATE'
            ) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'timestamp':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $field_value)
                && $field_value !== 'CURRENT_TIMESTAMP'
            ) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'time':
            if (!preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $field_value)
                && $field_value !== 'CURRENT_TIME'
            ) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'float':
        case 'double':
            if ($field_value != (double)$field_value) {
                /*return schem.raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            $field_value = (double) $field_value;
            break;
        }
        return true;
    }
}

?>
