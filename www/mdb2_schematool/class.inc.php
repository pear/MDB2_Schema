<?php /* vim: se et ts=4 sw=4 sts=4 fdm=marker tw=80: */
/**
 * Copyright (c) 1998-2010 Manuel Lemos, Tomas V.V.Cox,
 * Stig. S. Bakken, Lukas Smith, Igor Feghali
 * All rights reserved.
 *
 * MDB2_Schema enables users to maintain RDBMS independant schema files
 * in XML that can be used to manipulate both data and database schemas
 * This LICENSE is in the BSD license style.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,
 * Lukas Smith, Igor Feghali nor the names of his contributors may be
 * used to endorse or promote products derived from this software
 * without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE
 * REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 *  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
 * WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5
 *
 * @category Database
 * @package  MDB2_Schema
 * @author   Igor Feghali <ifeghali@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  SVN: $Id$
 * @link     http://pear.php.net/packages/MDB2_Schema
 */

/**
 * An example of how to use MDB2_Schema
 *
 * This is all rather ugly code, thats probably very much XSS exploitable etc.
 * However the idea was to keep the magic and dependencies low, to just
 * illustrate the MDB2_Schema API a bit.
 *
 * @category Database
 * @package  MDB2_Schema
 * @author   Igor Feghali <ifeghali@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://pear.php.net/packages/MDB2_Schema
 */
class MDB2_Schema_Example
{
    var $options = array(
        'log_line_break' => '<br>',
        'idxname_format' => '%s',
        'DBA_username' => '',
        'DBA_password' => '',
        'default_table_type' => 'MyISAM',
        'debug' => true,
        'use_transactions' => true,
        'quote_identifier' => true,
        'force_defaults' => false,
        'portability' => false,
        'drop_obsolete_objects' => false,
    );

    var $dsn = array(
        'phptype'   => '',
        'username'  => 'root',
        'password'  => '',
        'hostspec'  => 'localhost',
        'database'  => 'MDB2Example',
        'charset'   => 'utf8'
    );

    var $show_structure = false;
    var $disable_query = false;
    var $action = '';
    var $dumptype = '';
    var $file = '';

    /**
     * Creates a MDB2_Schema_Example instance
     *
     * This wraper is required for user input validation. If everything goes
     * well, save user's options and return an object.
     *
     * @param array $input mixed array with user actions information
     *
     * @return object PEAR_Error|MDB2_Schema_Example
     *
     * @access public
     * @static
     */
    public static function factory($input)
    {
        $obj = new MDB2_Schema_Example($input);
        if ($error = $obj->validateInput($input)) {
            return PEAR::raiseError($error);
        } else {
            $obj->saveCookies();
            $obj->setOptions($input);
            return $obj;
        }
    }

    /**
     * Sets MDB2_Schema_Example options according to user input
     *
     * If an option is missing, force its value to be FALSE. This is required to 
     * catch unmarked checkboxes.
     *
     * @param array $options mixed array with user actions information
     *
     * @return void
     *
     * @access public
     * @static
     */
    function setOptions($options)
    {
        foreach ($this->options as $k => $v) {
            if (is_string($v)) {
                if (isset($options[$k])) {
                    $this->options[$k] = $options[$k];
                } else {
                    $this->options[$k] = '';
                }
            } else {
                if ((isset($options[$k])) && (!empty($options[$k]))) {
                    $this->options[$k] = true;
                } else {
                    $this->options[$k] = false;
                } 
            }
        }

        $this->dsn = array(
            'phptype'   => $options['type'],
            'username'  => $options['user'],
            'password'  => $options['pass'],
            'hostspec'  => $options['host'],
            'database'  => $options['name'],
            'charset'   => $options['char']
        );
    }

    /**
     * Validates user input
     *
     * @param array $input mixed array with user actions information
     *
     * @return string|boolean return string on error or boolean false
     *
     * @access public
     * @static
     */
    function validateInput($input)
    {
        if (!array_key_exists('action', $input)) {
            return 'Script Error: no action selected';
        }
        switch ($input['action']) {
        case 'dump':
            if (!array_key_exists('dumptype', $input)) {
                return 'no dump type specified';
            }
            $this->dumptype = $input['dumptype'];
            if (!array_key_exists('file', $input)) {
                return 'no output file specified';
            }
            $this->file = $input['file'];
            break;

        case 'update':
        case 'create':
        case 'initialize':
            if (!array_key_exists('file', $input)) {
                return 'no input file specified';
            }
            $this->file = $input['file'];
            break;

        default:
            return 'Script Error: invalid action';
        }

        $this->action = $input['action'];

        if (isset($input['show_structure'])) {
            $this->show_structure = $input['show_structure'];
        } else {
            $this->show_structure = false;
        }

        if (isset($input['disable_query'])) {
            $this->disable_query = $input['disable_query'];
        } else {
            $this->disable_query = false;
        }

        return false;
    }

    /**
     * Saves user preferences as browser cookies
     *
     * @return void
     *
     * @access public
     * @static
     */
    function saveCookies()
    {
        setcookie('use_transactions', $this->options['use_transactions']);
        setcookie('default_table_type', $this->options['default_table_type']);
        setcookie('log_line_break', $this->options['log_line_break']);
        setcookie('idxname_format', $this->options['idxname_format']);
        setcookie('DBA_username', $this->options['DBA_username']);
        setcookie('DBA_password', $this->options['DBA_password']);
        setcookie('debug', $this->options['debug']);
        setcookie('quote_identifier', $this->options['quote_identifier']);
        setcookie('force_defaults', $this->options['force_defaults']);
        setcookie('portability', $this->options['portability']);
        setcookie('drop_obsolete_objects', $this->options['drop_obsolete_objects']);
        
        setcookie('disable_query', $this->disable_query);
        setcookie('action', $this->action);
        setcookie('dumptype', $this->dumptype);
        setcookie('file', $this->file);
        setcookie('show_structure', $this->show_structure);

        setcookie('username', $this->dsn['username']);
        setcookie('password', $this->dsn['password']);
        setcookie('hostspec', $this->dsn['hostspec']);
        setcookie('database', $this->dsn['database']);
        setcookie('charset', $this->dsn['charset']);

        setcookie('loaded', '1');
    }
}
