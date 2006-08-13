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

    /* Definition */
    function validateTable(&$schema)
    {
        if (!$schema->table_name) {
            $schema->raiseError('a table has to have a name', null, $xp);
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->table_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $schema->raiseError('table name "'.$schema->table_name.'" is a reserved word in: '.$rdbms, null, $xp);
                }
            }
        }

        if (isset($schema->database_definition['tables'][$schema->table_name])) {
            $schema->raiseError('table "'.$schema->table_name.'" already exists', null, $xp);
        }

        if (empty($schema->table['was'])) {
            $schema->table['was'] = $schema->table_name;
        }

        $autoinc = $primary = false;
        if (empty($schema->table['fields']) || !is_array($schema->table['fields'])) {
            $schema->raiseError('tables need one or more fields', null, $xp);
        } else {
            foreach ($schema->table['fields'] as $field_name => $field) {
                if (!empty($field['autoincrement'])) {
                    if ($primary) {
                        $schema->raiseError('there was already an autoincrement field in "'.$schema->table_name.'" before "'.$field_name.'"', null, $xp);
                    } else {
                        $autoinc = $primary = true;
                    }

                    if (!$schema->table['fields'][$field_name]['notnull']) {
                        $schema->raiseError('all autoincrement fields must be defined notnull in "'.$schema->table_name.'"', null, $xp);
                    }

                    if (empty($field['default'])) {
                        $schema->table['fields'][$field_name]['default'] = '0';
                    } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                        $schema->raiseError('all autoincrement fields must be defined default "0" in "'.$schema->table_name.'"', null, $xp);
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
                        $schema->raiseError('there was already an primary index or autoincrement field in "'.$schema->table_name.'" before "'.$name.'"', null, $xp);
                    } else {
                        $primary = true;
                    }
                }

                if (!$skip_index && is_array($index['fields'])) {
                    foreach ($index['fields'] as $field_name => $field) {
                        if (!isset($schema->table['fields'][$field_name])) {
                            $schema->raiseError('index field "'.$field_name.'" does not exist', null, $xp);
                        } elseif (!empty($index['primary'])
                            && !$schema->table['fields'][$field_name]['notnull']
                        ) {
                            $schema->raiseError('all primary key fields must be defined notnull in "'.$schema->table_name.'"', null, $xp);
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

    function validateField(&$schema)
    {
        if (!$schema->field_name) {
            $schema->raiseError('field name missing', null, $xp);
        } elseif (isset($schema->table['fields'][$schema->field_name])) {
            $schema->raiseError('field "'.$schema->field_name.'" already exists', null, $xp);
        }

        if ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->field_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $schema->raiseError('field name "'.$schema->field_name.'" is a reserved word in: '.$rdbms, null, $xp);
                }
            }
        }
        /* Type check */
        if (empty($schema->field['type'])) {
            $schema->raiseError('no field type specified', null, $xp);
        }
        if (!empty($schema->valid_types) && !array_key_exists($schema->field['type'], $schema->valid_types)) {
            $schema->raiseError('no valid field type ("'.$schema->field['type'].'") specified', null, $xp);
        }
        if (array_key_exists('unsigned', $schema->field) && !$schema->isBoolean($schema->field['unsigned'])) {
            $schema->raiseError('unsigned has to be a boolean value', null, $xp);
        }
        if (array_key_exists('fixed', $schema->field) && !$schema->isBoolean($schema->field['fixed'])) {
            $schema->raiseError('fixed has to be a boolean value', null, $xp);
        }
        if (array_key_exists('length', $schema->field) && $schema->field['length'] <= 0) {
            $schema->raiseError('length has to be an integer greater 0', null, $xp);
        }
        if (empty($schema->field['was'])) {
            $schema->field['was'] = $schema->field_name;
        }
        if (empty($schema->field['notnull'])) {
            $schema->field['notnull'] = false;
        }
        if (!$schema->isBoolean($schema->field['notnull'])) {
            $schema->raiseError('field "notnull" has to be a boolean value', null, $xp);
        }
        if ($schema->force_defaults
            && !array_key_exists('default', $schema->field)
            && $schema->field['type'] != 'clob' && $schema->field['type'] != 'blob'
        ) {
            $schema->field['default'] = $schema->valid_types[$schema->field['type']];
        }

        if (array_key_exists('default', $schema->field)) {
            if ($schema->field['type'] == 'clob' || $schema->field['type'] == 'blob') {
                $schema->raiseError('"'.$schema->field['type'].
                    '"-fields are not allowed to have a default value', null, $xp);
            }
            if ($schema->field['default'] === '') {
                if (!$schema->field['notnull']) {
                    $schema->field['default'] = null;
                }
            }
        }

        $schema->table['fields'][$schema->field_name] = $schema->field;

        if (isset($schema->field['default'])
            && !$schema->validateFieldValue($schema->field_name,
                $schema->table['fields'][$schema->field_name]['default'], $xp
            )
        ) {
            $schema->raiseError('default value of "'.$schema->field_name.'" is of wrong type', null, $xp);
        }
        return true;
    }

    function validateIndex(&$schema)
    {
        if (!$schema->index_name) {
            $schema->raiseError('an index has to have a name', null, $xp);
        }
        if (isset($schema->table['indexes'][$schema->index_name])) {
            $schema->raiseError('index "'.$schema->index_name.'" already exists', null, $xp);
        }
        if (array_key_exists('unique', $schema->index) && !$schema->isBoolean($schema->index['unique'])) {
            $schema->raiseError('field "unique" has to be a boolean value', null, $xp);
        }
        if (array_key_exists('primary', $schema->index) && !$schema->isBoolean($schema->index['primary'])) {
            $schema->raiseError('field "primary" has to be a boolean value', null, $xp);
        }

        if (empty($schema->index['was'])) {
            $schema->index['was'] = $schema->index_name;
        }
        $schema->table['indexes'][$schema->index_name] = $schema->index;
        return true;
    }

    function validateIndexField(&$schema)
    {
        if (!$schema->field_name) {
            $schema->raiseError('the index-field-name is required', null, $xp);
        }
        if (!empty($schema->field['sorting'])
            && $schema->field['sorting'] !== 'ascending' && $schema->field['sorting'] !== 'descending') {
            $schema->raiseError('sorting type unknown', null, $xp);
        } else {
            $schema->field['sorting'] = 'ascending';
        }
        $schema->index['fields'][$schema->field_name] = $schema->field;
        return true;
    }

    function validateTableName(&$schema)
    {
        if (isset($schema->structure['tables'][$schema->table_name])) {
            $schema->table = $schema->structure['tables'][$schema->table_name];
        }
        return true;
    }

    function validateSequence(&$schema)
    {
        if (!$schema->seq_name) {
            $schema->raiseError('a sequence has to have a name', null, $xp);
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->seq_name);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $schema->raiseError('sequence name "'.$schema->seq_name.'" is a reserved word in: '.$rdbms, null, $xp);
                }
            }
        }

        if (isset($schema->database_definition['sequences'][$schema->seq_name])) {
            $schema->raiseError('sequence "'.$schema->seq_name.'" already exists', null, $xp);
        }

        if (empty($schema->seq['was'])) {
            $schema->seq['was'] = $schema->seq_name;
        }

        if (!empty($schema->seq['on'])) {
            if (empty($schema->seq['on']['table']) || empty($schema->seq['on']['field'])) {
                $schema->raiseError('sequence "'.$schema->seq_name.
                    '" was not properly defined', null, $xp);
            }
        }
        $schema->database_definition['sequences'][$schema->seq_name] = $schema->seq;
        return true;
    }

    function validateDatabase(&$schema)
    {
        if (!isset($schema->database_definition['name']) || !$schema->database_definition['name']) {
            $schema->raiseError('a database has to have a name', null, $xp);
        } elseif ($schema->fail_on_invalid_names) {
            $name = strtoupper($schema->database_definition['name']);
            foreach ($schema->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $schema->raiseError('database name "'.$schema->database_definition['name'].'" is a reserved word in: '.$rdbms, null, $xp);
                }
            }
        }

        if (isset($schema->database_definition['create'])
            && !$schema->isBoolean($schema->database_definition['create'])
        ) {
            $schema->raiseError('field "create" has to be a boolean value', null, $xp);
        }
        if (isset($schema->database_definition['overwrite'])
            && !$schema->isBoolean($schema->database_definition['overwrite'])
        ) {
            $schema->raiseError('field "overwrite" has to be a boolean value', null, $xp);
        }

        if (isset($schema->database_definition['sequences'])) {
            foreach ($schema->database_definition['sequences'] as $seq_name => $seq) {
                if (!empty($seq['on'])
                    && empty($schema->database_definition['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                ) {
                    $schema->raiseError('sequence "'.$seq_name.
                        '" was assigned on unexisting field/table', null, $xp);
                }
            }
        }
        if (PEAR::isError($schema->error)) {
            $schema->database_definition = $schema->error;
        }
        return true;
    }

    /* Data Manipulation */
    function validateInsertField(&$schema)
    {
        if (!$schema->init_name) {
            $schema->raiseError('field-name has to be specified', null, $xp);
        }
        if (isset($schema->init['fields'][$schema->init_name])) {
            $schema->raiseError('field "'.$schema->init_name.'" already filled', null, $xp);
        }
        if (!isset($schema->table['fields'][$schema->init_name])) {
            $schema->raiseError('unknown field "'.$schema->init_name.'"', null, $xp);
        }
        if ($schema->init_value !== ''
            && !$schema->validateFieldValue($schema->init_name, $schema->init_value, $xp)
        ) {
            $schema->raiseError('field "'.$schema->init_name.'" has wrong value', null, $xp);
        }
        $schema->init['fields'][$schema->init_name] = $schema->init_value;
        return true;
    }

    function validateInsert(&$schema)
    {
        $schema->table['initialization'][] = $schema->init;
        return true;
    }
}

?>
