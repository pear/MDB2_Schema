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
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'XML/Parser.php';

if (empty($GLOBALS['_MDB2_Schema_Reserved'])) {
    $GLOBALS['_MDB2_Schema_Reserved'] = array();
}

/**
 * Parses an XML schema file
 *
 * @package MDB2_Schema
 * @category Database
 * @access protected
 * @author  Christian Dickmann <dickmann@php.net>
 */
class MDB2_Schema_Parser extends XML_Parser
{
    var $database_definition = array();
    var $elements = array();
    var $element = '';
    var $count = 0;
    var $table = array();
    var $table_name = '';
    var $field = array();
    var $field_name = '';
    var $init = array();
    var $init_name = '';
    var $init_value = '';
    var $index = array();
    var $index_name = '';
    var $var_mode = false;
    var $variables = array();
    var $seq = array();
    var $seq_name = '';
    var $error;
    var $fail_on_invalid_names = true;
    var $structure = false;
    var $valid_types = array();

    function __construct($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array())
    {
        // force ISO-8859-1 due to different defaults for PHP4 and PHP5
        // todo: this probably needs to be investigated some more andcleaned up
        parent::XML_Parser('ISO-8859-1');
        $this->variables = $variables;
        if (is_array($fail_on_invalid_names)) {
            $this->fail_on_invalid_names
                = array_intersect($fail_on_invalid_names, array_keys($GLOBALS['_MDB2_Schema_Reserved']));
        } elseif ($this->fail_on_invalid_names === true) {
            $this->fail_on_invalid_names = array_keys($GLOBALS['_MDB2_Schema_Reserved']);
        } else {
            $this->fail_on_invalid_names = false;
        }
        $this->structure = $structure;
        $this->valid_types = $valid_types;
    }

    function MDB2_Schema_Parser($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array())
    {
        $this->__construct($variables, $fail_on_invalid_names, $structure, $valid_types);
    }

    function startHandler($xp, $element, $attribs)
    {
        if (strtolower($element) == 'variable') {
            $this->var_mode = true;
            return;
        }

        $this->elements[$this->count++] = strtolower($element);
        $this->element = implode('-', $this->elements);

        switch ($this->element) {
        case 'database-table-initialization-insert':
            $this->init = array('type' => 'insert');
            break;
        case 'database-table-initialization-insert-field':
            $this->init_name = '';
            $this->init_value = '';
            break;
        case 'database-table':
            $this->table_name = '';
            $this->table = array();
            break;
        case 'database-table-declaration-field':
            $this->field_name = '';
            $this->field = array();
            break;
        case 'database-table-declaration-field-default':
            $this->field['default'] = '';
            break;
        case 'database-table-declaration-index':
            $this->index_name = '';
            $this->index = array();
            break;
        case 'database-sequence':
            $this->seq_name = '';
            $this->seq = array();
            break;
        case 'database-table-declaration-index-field':
            $this->field_name = '';
            $this->field = array();
            break;
        }
    }

    function endHandler($xp, $element)
    {
        if (strtolower($element) == 'variable') {
            $this->var_mode = false;
            return;
        }

        switch ($this->element) {
        /* Initialization */
        case 'database-table-initialization-insert-field':
            if (!$this->init_name) {
                $this->raiseError('field-name has to be specified', null, $xp);
            }
            if (isset($this->init['fields'][$this->init_name])) {
                $this->raiseError('field "'.$this->init_name.'" already filled', null, $xp);
            }
            if (!isset($this->table['fields'][$this->init_name])) {
                $this->raiseError('unknown field "'.$this->init_name.'"', null, $xp);
            }
            if ($this->init_value !== ''
                && !$this->validateFieldValue($this->init_name, $this->init_value, $xp)
            ) {
                $this->raiseError('field "'.$this->init_name.'" has wrong value', null, $xp);
            }
            $this->init['fields'][$this->init_name] = $this->init_value;
            break;
        case 'database-table-initialization-insert':
            $this->table['initialization'][] = $this->init;
            break;

        /* Table definition */
        case 'database-table':
            if (!$this->table_name) {
                $this->raiseError('a table has to have a name', null, $xp);
            } elseif ($this->fail_on_invalid_names) {
                $name = strtoupper($this->table_name);
                foreach ($this->fail_on_invalid_names as $rdbms) {
                    if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                        $this->raiseError('table name "'.$this->table_name.'" is a reserved word in: '.$rdbms, null, $xp);
                        break;
                    }
                }
            }

            if (isset($this->database_definition['tables'][$this->table_name])) {
                $this->raiseError('table "'.$this->table_name.'" already exists', null, $xp);
            }

            if (empty($this->table['was'])) {
                $this->table['was'] = $this->table_name;
            }

            $autoinc = $primary = false;
            if (empty($this->table['fields']) || !is_array($this->table['fields'])) {
                $this->raiseError('tables need one or more fields', null, $xp);
            } else {
                foreach ($this->table['fields'] as $field_name => $field) {
                    if (!empty($field['autoincrement'])) {
                        if ($primary) {
                            $this->raiseError('there was already an autoincrement field in "'.$this->table_name.'" before "'.$field_name.'"', null, $xp);
                        } else {
                            $autoinc = $primary = true;
                        }

                        if (!$this->table['fields'][$field_name]['notnull']) {
                            $this->raiseError('all autoincrement fields must be defined notnull in "'.$this->table_name.'"', null, $xp);
                        }

                        if (empty($field['default'])) {
                            $this->table['fields'][$field_name]['default'] = '0';
                        } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                            $this->raiseError('all autoincrement fields must be defined default "0" in "'.$this->table_name.'"', null, $xp);
                        }
                    }
                }
            }
            if (!empty($this->table['indexes']) && is_array($this->table['indexes'])) {
                foreach ($this->table['indexes'] as $name => $index) {
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
                            $this->raiseError('there was already an primary index or autoincrement field in "'.$this->table_name.'" before "'.$name.'"', null, $xp);
                        } else {
                            $primary = true;
                        }
                    }

                    if (!$skip_index && is_array($index['fields'])) {
                        foreach ($index['fields'] as $field_name => $field) {
                            if (!isset($this->table['fields'][$field_name])) {
                                $this->raiseError('index field "'.$field_name.'" does not exist', null, $xp);
                            } elseif (!empty($index['primary'])
                                && !$this->table['fields'][$field_name]['notnull']
                            ) {
                                $this->raiseError('all primary key fields must be defined notnull in "'.$this->table_name.'"', null, $xp);
                            }
                        }
                    } else {
                        unset($this->table['indexes'][$name]);
                    }
                }
            }
            $this->database_definition['tables'][$this->table_name] = $this->table;
            break;

        /* Field declaration */
        case 'database-table-declaration-field':
            if (!$this->field_name) {
                $this->raiseError('field name missing', null, $xp);
            } elseif (isset($this->table['fields'][$this->field_name])) {
                $this->raiseError('field "'.$this->field_name.'" already exists', null, $xp);
            }

            if ($this->fail_on_invalid_names) {
                $name = strtoupper($this->field_name);
                foreach ($this->fail_on_invalid_names as $rdbms) {
                    if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                        $this->raiseError('field name "'.$this->field_name.'" is a reserved word in: '.$rdbms, null, $xp);
                        break;
                    }
                }
            }
            /* Type check */
            if (empty($this->field['type'])) {
                $this->raiseError('no field type specified', null, $xp);
            }
            if (!empty($this->valid_types) && !array_key_exists($this->field['type'], $this->valid_types)) {
                $this->raiseError('no valid field type ("'.$this->field['type'].'") specified', null, $xp);
            }
            if (array_key_exists('unsigned', $this->field) && !$this->isBoolean($this->field['unsigned'])) {
                $this->raiseError('unsigned has to be a boolean value', null, $xp);
            }
            if (array_key_exists('fixed', $this->field) && !$this->isBoolean($this->field['fixed'])) {
                $this->raiseError('fixed has to be a boolean value', null, $xp);
            }
            if (array_key_exists('length', $this->field) && $this->field['length'] <= 0) {
                $this->raiseError('length has to be an integer greater 0', null, $xp);
            }
            if (empty($this->field['was'])) {
                $this->field['was'] = $this->field_name;
            }
            if (empty($this->field['notnull'])) {
                $this->field['notnull'] = false;
            }
            if (!$this->isBoolean($this->field['notnull'])) {
                $this->raiseError('field "notnull" has to be a boolean value', null, $xp);
            }
            if (!array_key_exists('default', $this->field)
                && $this->field['type'] != 'clob' && $this->field['type'] != 'blob'
            ) {
                $this->field['default'] = $this->valid_types[$this->field['type']];
            }

            if (array_key_exists('default', $this->field)) {
                if ($this->field['type'] == 'clob' || $this->field['type'] == 'blob') {
                    $this->raiseError('"'.$this->field['type'].
                        '"-fields are not allowed to have a default value', null, $xp);
                }
                if ($this->field['default'] === '') {
                    if (!$this->field['notnull']) {
                        $this->field['default'] = null;
                    }
                }
            }

            $this->table['fields'][$this->field_name] = $this->field;

            if (isset($this->field['default'])
                && !$this->validateFieldValue($this->field_name,
                    $this->table['fields'][$this->field_name]['default'], $xp
                )
            ) {
                $this->raiseError('default value of "'.$this->field_name.'" is of wrong type', null, $xp);
            }
            break;

        /* Index declaration */
        case 'database-table-declaration-index':
            if (!$this->index_name) {
                $this->raiseError('an index has to have a name', null, $xp);
            }
            if (isset($this->table['indexes'][$this->index_name])) {
                $this->raiseError('index "'.$this->index_name.'" already exists', null, $xp);
            }
            if (array_key_exists('unique', $this->index) && !$this->isBoolean($this->index['unique'])) {
                $this->raiseError('field "unique" has to be a boolean value', null, $xp);
            }
            if (array_key_exists('primary', $this->index) && !$this->isBoolean($this->index['primary'])) {
                $this->raiseError('field "primary" has to be a boolean value', null, $xp);
            }

            if (empty($this->index['was'])) {
                $this->index['was'] = $this->index_name;
            }
            $this->table['indexes'][$this->index_name] = $this->index;
            break;
        case 'database-table-declaration-index-field':
            if (!$this->field_name) {
                $this->raiseError('the index-field-name is required', null, $xp);
            }
            if (!empty($this->field['sorting'])
                && $this->field['sorting'] !== 'ascending' && $this->field['sorting'] !== 'descending') {
                $this->raiseError('sorting type unknown', null, $xp);
            } else {
                $this->field['sorting'] = 'ascending';
            }
            $this->index['fields'][$this->field_name] = $this->field;
            break;
        case 'database-table-name':
            if (isset($this->structure['tables'][$this->table_name])) {
                $this->table = $this->structure['tables'][$this->table_name];
            }
            break;

        /* Sequence declaration */
        case 'database-sequence':
            if (!$this->seq_name) {
                $this->raiseError('a sequence has to have a name', null, $xp);
            } elseif ($this->fail_on_invalid_names) {
                $name = strtoupper($this->seq_name);
                foreach ($this->fail_on_invalid_names as $rdbms) {
                    if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                        $this->raiseError('sequence name "'.$this->seq_name.'" is a reserved word in: '.$rdbms, null, $xp);
                        break;
                    }
                }
            }

            if (isset($this->database_definition['sequences'][$this->seq_name])) {
                $this->raiseError('sequence "'.$this->seq_name.'" already exists', null, $xp);
            }

            if (empty($this->seq['was'])) {
                $this->seq['was'] = $this->seq_name;
            }

            if (!empty($this->seq['on'])) {
                if (empty($this->seq['on']['table']) || empty($this->seq['on']['field'])) {
                    $this->raiseError('sequence "'.$this->seq_name.
                        '" was not properly defined', null, $xp);
                }
            }
            $this->database_definition['sequences'][$this->seq_name] = $this->seq;
            break;

        /* End of File */
        case 'database':
            if (!isset($this->database_definition['name']) || !$this->database_definition['name']) {
                $this->raiseError('a database has to have a name', null, $xp);
            } elseif ($this->fail_on_invalid_names) {
                $name = strtoupper($this->database_definition['name']);
                foreach ($this->fail_on_invalid_names as $rdbms) {
                    if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                        $this->raiseError('database name "'.$this->database_definition['name'].'" is a reserved word in: '.$rdbms, null, $xp);
                        break;
                    }
                }
            }

            if (isset($this->database_definition['create'])
                && !$this->isBoolean($this->database_definition['create'])
            ) {
                $this->raiseError('field "create" has to be a boolean value', null, $xp);
            }
            if (isset($this->database_definition['overwrite'])
                && !$this->isBoolean($this->database_definition['overwrite'])
            ) {
                $this->raiseError('field "overwrite" has to be a boolean value', null, $xp);
            }

            if (isset($this->database_definition['sequences'])) {
                foreach ($this->database_definition['sequences'] as $seq_name => $seq) {
                    if (!empty($seq['on'])
                        && empty($this->database_definition['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                    ) {
                        $this->raiseError('sequence "'.$seq_name.
                            '" was assigned on unexisting field/table', null, $xp);
                    }
                }
            }
            if (PEAR::isError($this->error)) {
                $this->database_definition = $this->error;
            }
            break;
        }

        unset($this->elements[--$this->count]);
        $this->element = implode('-', $this->elements);
    }

    function validateFieldValue($field_name, &$field_value, &$xp)
    {
        if (!isset($this->table['fields'][$field_name])) {
            return $this->raiseError('"'.$field_name.'" is not defined', null, $xp);
        }
        $field_def = $this->table['fields'][$field_name];
        switch ($field_def['type']) {
        case 'text':
        case 'clob':
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['length'].'"', null, $xp);
            }
            break;
        case 'blob':
            /*
            if (!preg_match('/^([0-9a-f]{2})*$/i', $field_value)) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            */
            $field_value = pack('H*', $field_value);
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError('"'.$field_value.'" is larger than "'.
                    $field_def['type'].'"', null, $xp);
            }
            break;
        case 'integer':
            if ($field_value != ((int)$field_value)) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            $field_value = (int) $field_value;
            if (!empty($field_def['unsigned']) && $field_def['unsigned'] && $field_value < 0) {
                return $this->raiseError('"'.$field_value.'" signed instead of unsigned', null, $xp);
            }
            break;
        case 'boolean':
            if (!$this->isBoolean($field_value)) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            break;
        case 'date':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $field_value)
                && $field_value !== 'CURRENT_DATE'
            ) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            break;
        case 'timestamp':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $field_value)
                && $field_value !== 'CURRENT_TIMESTAMP'
            ) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            break;
        case 'time':
            if (!preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $field_value)
                && $field_value !== 'CURRENT_TIME'
            ) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            break;
        case 'float':
        case 'double':
            if ($field_value != (double)$field_value) {
                return $this->raiseError('"'.$field_value.'" is not of type "'.
                    $field_def['type'].'"', null, $xp);
            }
            $field_value = (double) $field_value;
            break;
        }
        return true;
    }

    function &raiseError($msg = null, $ecode = 0, $xp = null)
    {
        if (is_null($this->error)) {
            $error = '';
            if (is_resource($msg)) {
                $error .= 'Parser error: '.xml_error_string(xml_get_error_code($msg));
                $xp = $msg;
            } else {
                $error .= 'Parser error: '.$msg;
                if (!is_resource($xp)) {
                    $xp = $this->parser;
                }
            }
            if ($error_string = xml_error_string($ecode)) {
                $error .= ' - '.$error_string;
            }
            if (is_resource($xp)) {
                $byte = @xml_get_current_byte_index($xp);
                $line = @xml_get_current_line_number($xp);
                $column = @xml_get_current_column_number($xp);
                $error .= " - Byte: $byte; Line: $line; Col: $column";
            }
            $error .= "\n";
            $this->error =& MDB2::raiseError(MDB2_SCHEMA_ERROR_PARSE, null, null, $error);
        }
        return $this->error;
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

    function cdataHandler($xp, $data)
    {
        if ($this->var_mode == true) {
            if (!isset($this->variables[$data])) {
                $this->raiseError('variable "'.$data.'" not found', null, $xp);
                return;
            }
            $data = $this->variables[$data];
        }

        switch ($this->element) {
        /* Initialization */
        case 'database-table-initialization-insert-field-name':
            if (isset($this->init_name)) {
                $this->init_name .= $data;
            } else {
                $this->init_name = $data;
            }
            break;
        case 'database-table-initialization-insert-field-value':
            if (isset($this->init_value)) {
                $this->init_value .= $data;
            } else {
                $this->init_value = $data;
            }
            break;

        /* Database */
        case 'database-name':
            if (isset($this->database_definition['name'])) {
                $this->database_definition['name'] .= $data;
            } else {
                $this->database_definition['name'] = $data;
            }
            break;
        case 'database-create':
            if (isset($this->database_definition['create'])) {
                $this->database_definition['create'] .= $data;
            } else {
                $this->database_definition['create'] = $data;
            }
            break;
        case 'database-overwrite':
            if (isset($this->database_definition['overwrite'])) {
                $this->database_definition['overwrite'] .= $data;
            } else {
                $this->database_definition['overwrite'] = $data;
            }
            break;
        case 'database-table-name':
            if (isset($this->table_name)) {
                $this->table_name .= $data;
            } else {
                $this->table_name = $data;
            }
            break;
        case 'database-table-was':
            if (array_key_exists($this->table['was'])) {
                $this->table['was'] .= $data;
            } else {
                $this->table['was'] = $data;
            }
            break;

        /* Field declaration */
        case 'database-table-declaration-field-name':
            if (isset($this->field_name)) {
                $this->field_name .= $data;
            } else {
                $this->field_name = $data;
            }
            break;
        case 'database-table-declaration-field-type':
            if (isset($this->field['type'])) {
                $this->field['type'] .= $data;
            } else {
                $this->field['type'] = $data;
            }
            break;
        case 'database-table-declaration-field-was':
            if (isset($this->field['was'])) {
                $this->field['was'] .= $data;
            } else {
                $this->field['was'] = $data;
            }
            break;
        case 'database-table-declaration-field-notnull':
            if (isset($this->field['notnull'])) {
                $this->field['notnull'] .= $data;
            } else {
                $this->field['notnull'] = $data;
            }
            break;
        case 'database-table-declaration-field-fixed':
            if (isset($this->field['fixed'])) {
                $this->field['fixed'] .= $data;
            } else {
                $this->field['fixed'] = $data;
            }
            break;
        case 'database-table-declaration-field-unsigned':
            if (isset($this->field['unsigned'])) {
                $this->field['unsigned'] .= $data;
            } else {
                $this->field['unsigned'] = $data;
            }
            break;
        case 'database-table-declaration-field-autoincrement':
            if (isset($this->field['autoincrement'])) {
                $this->field['autoincrement'] .= $data;
            } else {
                $this->field['autoincrement'] = $data;
            }
            break;
        case 'database-table-declaration-field-default':
            if (isset($this->field['default'])) {
                $this->field['default'] .= $data;
            } else {
                $this->field['default'] = $data;
            }
            break;
        case 'database-table-declaration-field-length':
            if (isset($this->field['length'])) {
                $this->field['length'] .= $data;
            } else {
                $this->field['length'] = $data;
            }
            break;

        /* Index declaration */
        case 'database-table-declaration-index-name':
            if (isset($this->index_name)) {
                $this->index_name .= $data;
            } else {
                $this->index_name = $data;
            }
            break;
        case 'database-table-declaration-index-primary':
            if (isset($this->index['primary'])) {
                $this->index['primary'] .= $data;
            } else {
                $this->index['primary'] = $data;
            }
            break;
        case 'database-table-declaration-index-unique':
            if (isset($this->index['unique'])) {
                $this->index['unique'] .= $data;
            } else {
                $this->index['unique'] = $data;
            }
            break;
        case 'database-table-declaration-index-was':
            if (isset($this->index['was'])) {
                $this->index['was'] .= $data;
            } else {
                $this->index['was'] = $data;
            }
            break;
        case 'database-table-declaration-index-field-name':
            if (isset($this->field_name)) {
                $this->field_name .= $data;
            } else {
                $this->field_name = $data;
            }
            break;
        case 'database-table-declaration-index-field-sorting':
            if (isset($this->field['sorting'])) {
                $this->field['sorting'] .= $data;
            } else {
                $this->field['sorting'] = $data;
            }
            break;
        /* Add by Leoncx */
        case 'database-table-declaration-index-field-length':
            if (isset($this->field['length'])) {
                $this->field['length'] .= $data;
            } else {
                $this->field['length'] = $data;
            }
            break;

        /* Sequence declaration */
        case 'database-sequence-name':
            if (isset($this->seq_name)) {
                $this->seq_name .= $data;
            } else {
                $this->seq_name = $data;
            }
            break;
        case 'database-sequence-was':
            if (isset($this->seq['was'])) {
                $this->seq['was'] .= $data;
            } else {
                $this->seq['was'] = $data;
            }
            break;
        case 'database-sequence-start':
            if (isset($this->seq['start'])) {
                $this->seq['start'] .= $data;
            } else {
                $this->seq['start'] = $data;
            }
            break;
        case 'database-sequence-on-table':
            if (isset($this->seq['on']['table'])) {
                $this->seq['on']['table'] .= $data;
            } else {
                $this->seq['on']['table'] = $data;
            }
            break;
        case 'database-sequence-on-field':
            if (isset($this->seq['on']['field'])) {
                $this->seq['on']['field'] .= $data;
            } else {
                $this->seq['on']['field'] = $data;
            }
            break;
        }
    }
}

?>
