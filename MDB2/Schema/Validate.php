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
        if (!$schema->table_name) {
            //$this->parser->raiseError('a table has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_TABLE_NAME;
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->table_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return $schema->raiseError('table name "'.$schema->table_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_TABLE_NAME;
                }
            }
        }

        if (isset($schema->database_definition['tables'][$schema->table_name])) {
            //$schema->raiseError('table "'.$schema->table_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_TABLE_EXISTS;
        }

        if (empty($schema->table['was'])) {
            $schema->table['was'] = $schema->table_name;
        }

        $autoinc = $primary = false;
        if (empty($schema->table['fields']) || !is_array($schema->table['fields'])) {
            //$schema->raiseError('tables need one or more fields', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELDS;
        } else {
            foreach ($schema->table['fields'] as $field_name => $field) {
                if (!empty($field['autoincrement'])) {
                    if ($primary) {
                        //$schema->raiseError('there was already an autoincrement field in "'.$schema->table_name.'" before "'.$field_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_DUP_AUTOINC;
                    } else {
                        $autoinc = $primary = true;
                    }

                    if (!$schema->table['fields'][$field_name]['notnull']) {
                        //$schema->raiseError('all autoincrement fields must be defined notnull in "'.$schema->table_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_AUTOINC_NULL;
                    }

                    if (empty($field['default'])) {
                        $schema->table['fields'][$field_name]['default'] = '0';
                    } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                        //$schema->raiseError('all autoincrement fields must be defined default "0" in "'.$schema->table_name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_AUTOINC_INVALID_DEFAULT;
                    }
                }
            }
        }
        if (!empty($schema->table['indexes']) && is_array($schema->table['indexes'])) {
            foreach ($schema->table['indexes'] as $name => $index) {
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
                        //$schema->raiseError('there was already an primary index or autoincrement field in "'.$schema->table_name.'" before "'.$name.'"', null, $xp);
                        return MDB2_SCHEMA_VALIDATE_PRIMARY_EXISTS;
                    } else {
                        $primary = true;
                    }
                }

                if (!$skip_index && is_array($index['fields'])) {
                    foreach ($index['fields'] as $field_name => $field) {
                        if (!isset($schema->table['fields'][$field_name])) {
                            //$schema->raiseError('index field "'.$field_name.'" does not exist', null, $xp);
                            return MDB2_SCHEMA_VALIDATE_BROKEN_INDEX;
                        } elseif (!empty($index['primary'])
                            && !$schema->table['fields'][$field_name]['notnull']
                        ) {
                            //$schema->raiseError('all primary key fields must be defined notnull in "'.$schema->table_name.'"', null, $xp);
                            return MDB2_SCHEMA_VALIDATE_PRIMARY_NULL;
                        }
                    }
                } else {
                    unset($schema->table['indexes'][$name]);
                }
            }
        }
        $schema->database_definition['tables'][$schema->table_name] = $schema->table;
        return true;
    }

    function validateField(&$fields, &$field, $field_name, $force_defaults, $valid_types = array(), $fail_on_invalid_names = array(), $xp)
    {
        if (!$schema->field_name) {
            //$schema->raiseError('field name missing', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELD_NAME;
        } elseif (isset($schema->table['fields'][$schema->field_name])) {
            //$schema->raiseError('field "'.$schema->field_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_FIELD_EXISTS;
        }

        if ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->field_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return $schema->raiseError('field name "'.$schema->field_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_FIELD_NAME;
                }
            }
        }
        /* Type check */
        if (empty($schema->field['type'])) {
            //return $schema->raiseError('no field type specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_FIELD_TYPE;
        }
        if (!empty($schema->valid_types) && !array_key_exists($schema->field['type'], $schema->valid_types)) {
            //$schema->raiseError('no valid field type ("'.$schema->field['type'].'") specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_FIELD_TYPE;
        }
        if (array_key_exists('unsigned', $schema->field) && !$this->isBoolean($schema->field['unsigned'])) {
            //$schema->raiseError('unsigned has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_UNSIGNED;
        }
        if (array_key_exists('fixed', $schema->field) && !$this->isBoolean($schema->field['fixed'])) {
            //$schema->raiseError('fixed has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_FIXED;
        }
        if (array_key_exists('length', $schema->field) && $schema->field['length'] <= 0) {
            //$schema->raiseError('length has to be an integer greater 0', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_LENGTH;
        }
        if (empty($schema->field['was'])) {
            $schema->field['was'] = $schema->field_name;
        }
        if (empty($schema->field['notnull'])) {
            $schema->field['notnull'] = false;
        }
        if (!$this->isBoolean($schema->field['notnull'])) {
            //$schema->raiseError('field "notnull" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_NOTNULL;
        }
        if ($schema->force_defaults
            && !array_key_exists('default', $schema->field)
            && $schema->field['type'] != 'clob' && $schema->field['type'] != 'blob'
        ) {
            $schema->field['default'] = $schema->valid_types[$schema->field['type']];
        }

        if (array_key_exists('default', $schema->field)) {
            if ($schema->field['type'] == 'clob' || $schema->field['type'] == 'blob') {
                /*$schema->raiseError('"'.$schema->field['type'].
                    '"-fields are not allowed to have a default value', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DEFAULT_NOT_ALLOWED;
            }
            if ($schema->field['default'] === '') {
                if (!$schema->field['notnull']) {
                    $schema->field['default'] = null;
                }
            }
        }

        $schema->table['fields'][$schema->field_name] = $schema->field;

        if (isset($schema->field['default'])
            && !$this->validateFieldValue($schema, $schema->field_name,
                $schema->table['fields'][$schema->field_name]['default'], $xp
            )
        ) {
            //$schema->raiseError('default value of "'.$schema->field_name.'" is of wrong type', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_DEFAULT;
        }
        return true;
    }

    function validateIndex(&$table_indexes, &$index, $index_name, $xp)
    {
        if (!$schema->index_name) {
            //$schema->raiseError('an index has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_INDEX_NAME;
        }
        if (isset($schema->table['indexes'][$schema->index_name])) {
            //$schema->raiseError('index "'.$schema->index_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INDEX_EXISTS;
        }
        if (array_key_exists('unique', $schema->index) && !$this->isBoolean($schema->index['unique'])) {
            //$schema->raiseError('field "unique" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_UNIQUE;
        }
        if (array_key_exists('primary', $schema->index) && !$this->isBoolean($schema->index['primary'])) {
            //$schema->raiseError('field "primary" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_PRIMARY;
        }

        if (empty($schema->index['was'])) {
            $schema->index['was'] = $schema->index_name;
        }
        $schema->table['indexes'][$schema->index_name] = $schema->index;
        return true;
    }

    function validateIndexField(&$index_fields, &$field, $field_name, $xp)
    {
        if (!$schema->field_name) {
            //$schema->raiseError('the index-field-name is required', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_INDEX_FIELD_NAME;
        }
        if (!empty($schema->field['sorting'])
            && $schema->field['sorting'] !== 'ascending' && $schema->field['sorting'] !== 'descending') {
            //$schema->raiseError('sorting type unknown', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_SORT;
        } else {
            $schema->field['sorting'] = 'ascending';
        }
        $schema->index['fields'][$schema->field_name] = $schema->field;
        return true;
    }

    function validateTableName(&$table, $table_name, $structure_tables, $xp)
    {
        if (isset($schema->structure['tables'][$schema->table_name])) {
            $schema->table = $schema->structure['tables'][$schema->table_name];
        }
        return true;
    }

    function validateSequence(&$sequences, &$sequence, $seq_name, $fail_on_invalid_names = array(), $xp)
    {
        if (!$schema->seq_name) {
            //$schema->raiseError('a sequence has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_SEQUENCE_NAME;
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->seq_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return $schema->raiseError('sequence name "'.$schema->seq_name.'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE_NAME;
                }
            }
        }

        if (isset($schema->database_definition['sequences'][$schema->seq_name])) {
            //$schema->raiseError('sequence "'.$schema->seq_name.'" already exists', null, $xp);
            return MDB2_SCHEMA_VALIDATE_SEQUENCE_EXISTS;
        }

        if (empty($schema->seq['was'])) {
            $schema->seq['was'] = $schema->seq_name;
        }

        if (!empty($schema->seq['on'])) {
            if (empty($schema->seq['on']['table']) || empty($schema->seq['on']['field'])) {
                /*$schema->raiseError('sequence "'.$schema->seq_name.
                    '" was not properly defined', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_INVALID_SEQUENCE;
            }
        }
        $schema->database_definition['sequences'][$schema->seq_name] = $schema->seq;
        return true;
    }

    function validateDatabase(&$database, $fail_on_invalid_names = array(), $xp)
    {
        if (!isset($schema->database_definition['name']) || !$schema->database_definition['name']) {
            //$schema->raiseError('a database has to have a name', null, $xp);
            return MDB2_SCHEMA_VALIDATE_NO_DATABASE_NAME;
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->database_definition['name']);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    //return $schema->raiseError('database name "'.$schema->database_definition['name'].'" is a reserved word in: '.$rdbms, null, $xp);
                    return MDB2_SCHEMA_VALIDATE_INVALID_DATABASE_NAME;
                }
            }
        }

        if (isset($schema->database_definition['create'])
            && !$this->isBoolean($schema->database_definition['create'])
        ) {
            //$schema->raiseError('field "create" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_CREATE;
        }
        if (isset($schema->database_definition['overwrite'])
            && !$this->isBoolean($schema->database_definition['overwrite'])
        ) {
            //$schema->raiseError('field "overwrite" has to be a boolean value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_INVALID_OVERWRITE;
        }

        if (isset($schema->database_definition['sequences'])) {
            foreach ($schema->database_definition['sequences'] as $seq_name => $seq) {
                if (!empty($seq['on'])
                    && empty($schema->database_definition['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                ) {
                    /*$schema->raiseError('sequence "'.$seq_name.
                        '" was assigned on unexisting field/table', null, $xp);*/
                    return MDB2_SCHEMA_VALIDATE_BROKEN_SEQUENCE;
                }
            }
        }
        if (PEAR::isError($schema->error)) {
            $schema->database_definition = $schema->error;
        }
        return true;
    }

    /* Data Manipulation */
    function validateInsertField(&$instruction, &$table, $field_name, $value, $xp)
    {
        if (!$schema->init_name) {
            //$schema->raiseError('field-name has to be specified', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_NO_FIELD_NAME;
        }
        if (isset($schema->init['fields'][$schema->init_name])) {
            //$schema->raiseError('field "'.$schema->init_name.'" already filled', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_EXISTS;
        }
        if (!isset($schema->table['fields'][$schema->init_name])) {
            //$schema->raiseError('unknown field "'.$schema->init_name.'"', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_NOT_FOUND;
        }
        if ($schema->init_value !== ''
            && !$this->validateFieldValue($schema, $schema->init_name, $schema->init_value, $xp)
        ) {
            //$schema->raiseError('field "'.$schema->init_name.'" has wrong value', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_INVALID_FIELD_VALUE;
        }
        $schema->init['fields'][$schema->init_name] = $schema->init_value;
        return true;
    }

    function validateDML(&$table, &$instruction, $xp)
    {
        $schema->table['initialization'][] = $schema->init;
        return true;
    }

    function validateFieldValue($table, $field_name, &$field_value, $xp)
    {
        if (!isset($schema->table['fields'][$field_name])) {
            //return $schema->raiseError('"'.$field_name.'" is not defined', null, $xp);
            return MDB2_SCHEMA_VALIDATE_DML_FIELD_NOT_FOUND;
        }
        $field_def = $schema->table['fields'][$field_name];
        switch ($field_def['type']) {
        case 'text':
        case 'clob':
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                /*return $schema->raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['length'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_VALUE_TOO_BIG;
            }
            break;
        case 'blob':
            /*
            if (!preg_match('/^([0-9a-f]{2})*$/i', $field_value)) {
                return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            */
            $field_value = pack('H*', $field_value);
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                /*return $schema->raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_VALUE_TOO_BIG;
            }
            break;
        case 'integer':
            if ($field_value != ((int)$field_value)) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            $field_value = (int) $field_value;
            if (!empty($field_def['unsigned']) && $field_def['unsigned'] && $field_value < 0) {
                //return $schema->raiseError('"'.$field_value.'" signed instead of unsigned', null, $xp);
                return MDB2_SCHEMA_VALIDATE_DML_NEGATIVE_VALUE_FOR_SIGNED_FIELD;
            }
            break;
        case 'boolean':
            if (!$this->isBoolean($field_value)) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'date':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $field_value)
                && $field_value !== 'CURRENT_DATE'
            ) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'timestamp':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $field_value)
                && $field_value !== 'CURRENT_TIMESTAMP'
            ) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'time':
            if (!preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $field_value)
                && $field_value !== 'CURRENT_TIME'
            ) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);*/
                return MDB2_SCHEMA_VALIDATE_DML_INVALID_VALUE_TYPE;
            }
            break;
        case 'float':
        case 'double':
            if ($field_value != (double)$field_value) {
                /*return $schema->raiseError('"'.$field_value.'" is not of type "'.
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
