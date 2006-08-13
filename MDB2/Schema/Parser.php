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
require_once 'MDB2/Schema/Validate.php';

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
    var $force_defaults = true;
    var $val;

    function __construct($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array(), $force_defaults = true)
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
        $this->force_defaults = $force_defaults;
        $this->val = new MDB2_Schema_Validate;
    }

    function MDB2_Schema_Parser($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array(), $force_defaults = true)
    {
        $this->__construct($variables, $fail_on_invalid_names, $structure, $valid_types, $force_defaults);
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
            $result = $this->val->validateInsertField($this);
            break;
        case 'database-table-initialization-insert':
            $result = $this->val->validateInsert($this);
            break;

        /* Table definition */
        case 'database-table':
            $result = $this->val->validateTable($this);
            break;
        case 'database-table-name':
            $result = $this->val->validateTableName($this);
            break;

        /* Field declaration */
        case 'database-table-declaration-field':
            $result = $this->val->validateField($this);
            break;

        /* Index declaration */
        case 'database-table-declaration-index':
            $result = $this->val->validateIndex($this);
            break;
        case 'database-table-declaration-index-field':
            $result = $this->val->validateIndexField($this);
            break;

        /* Sequence declaration */
        case 'database-sequence':
            $result = $this->val->validateSequence($this);
            break;

        /* End of File */
        case 'database':
            $result = $this->val->validateDatabase($this);
            break;
        }

        unset($this->elements[--$this->count]);
        $this->element = implode('-', $this->elements);
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
            if (isset($this->table['was'])) {
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
