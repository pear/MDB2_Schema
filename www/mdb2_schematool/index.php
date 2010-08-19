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
 * @author   Lukas Smith <smith@pooteeweet.org>
 * @author   Igor Feghali <ifeghali@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  SVN: $Id$
 * @link     http://pear.php.net/packages/MDB2_Schema
 */

require_once "HTML/Template/IT.php";

/**
 * This is all rather ugly code, thats probably very much XSS exploitable etc.
 * However the idea was to keep the magic and dependencies low, to just
 * illustrate the MDB2_Schema API a bit.
 */

/**
 * Makes sure we are aware of cookies
 */
$_REQUEST = array_merge($_COOKIE, $_REQUEST);

if (!isset($_REQUEST['loaded'])) {
    include_once 'class.inc.php';
    $defaults = new MDB2_Schema_Example();
    $defaults->saveCookies();
    setcookie('loaded', '1');
    header('location: index.php');
    exit;
}

/**
 * Configures template engine
 */
$tpl = new HTML_Template_IT();
$tpl->loadTemplatefile('index.tpl', true, true);

/**
 * Database Info
 */
if (isset($_REQUEST['username']) && $_REQUEST['username']) {
    $tpl->setVariable('username', $_REQUEST['username']);
}
if (isset($_REQUEST['password']) && $_REQUEST['password']) {
    $tpl->setVariable('password', $_REQUEST['password']);
}
if (isset($_REQUEST['hostspec']) && $_REQUEST['hostspec']) {
    $tpl->setVariable('hostspec', $_REQUEST['hostspec']);
}
if (isset($_REQUEST['database']) && $_REQUEST['database']) {
    $tpl->setVariable('database', $_REQUEST['database']);
}
if (isset($_REQUEST['charset']) && $_REQUEST['charset']) {
    $tpl->setVariable('charset', $_REQUEST['charset']);
}
if (isset($_REQUEST['file']) && $_REQUEST['file']) {
    $tpl->setVariable('file', $_REQUEST['file']);
}

/**
 * Options
 */
if (isset($_REQUEST['log_line_break']) && $_REQUEST['log_line_break']) {
    $tpl->setVariable('log_line_break', $_REQUEST['log_line_break']);
}
if (isset($_REQUEST['idxname_format']) && $_REQUEST['idxname_format']) {
    $tpl->setVariable('idxname_format', $_REQUEST['idxname_format']);
}
if (isset($_REQUEST['DBA_username']) && $_REQUEST['DBA_username']) {
    $tpl->setVariable('DBA_username', $_REQUEST['DBA_username']);
}
if (isset($_REQUEST['DBA_password']) && $_REQUEST['DBA_password']) {
    $tpl->setVariable('DBA_password', $_REQUEST['DBA_password']);
}
if (isset($_REQUEST['default_table_type']) && $_REQUEST['default_table_type']) {
    $tpl->setVariable('default_table_type', $_REQUEST['default_table_type']);
}
if (isset($_REQUEST['debug']) && $_REQUEST['debug']) {
    $tpl->setVariable('debug', 'checked="checked"');
}
if (isset($_REQUEST['use_transactions']) && $_REQUEST['use_transactions']) {
    $tpl->setVariable('use_transactions', 'checked="checked"'); 
}
if (isset($_REQUEST['quote_identifier']) && $_REQUEST['quote_identifier']) {
    $tpl->setVariable('quote_identifier', 'checked="checked"');
}
if (isset($_REQUEST['force_defaults']) && $_REQUEST['force_defaults']) {
    $tpl->setVariable('force_defaults', 'checked="checked"');
}
if (isset($_REQUEST['portability']) && $_REQUEST['portability']) {
    $tpl->setVariable('portability', 'checked="checked"');
}
if (isset($_REQUEST['show_structure']) && $_REQUEST['show_structure']) {
    $tpl->setVariable('show_structure', 'checked="checked"');
}
if (isset($_REQUEST['disable_query']) && $_REQUEST['disable_query']) {
    $tpl->setVariable('disable_query', 'checked="checked"');
}
if (isset($_REQUEST['drop_obsolete_objects'])
    && $_REQUEST['drop_obsolete_objects']
) {
    $tpl->setVariable('drop_obsolete_objects', 'checked="checked"');
}

/**
 * User action
 */
if (isset($_REQUEST['action']) && $_REQUEST['action']) {
    switch ($_REQUEST['action']) {
    case 'dump':
        $tpl->setVariable('dump-selected', 'checked="checked"');
        if (isset($_REQUEST['dumptype'])) {
            switch($_REQUEST['dumptype']) {
            case 'all':
                $tpl->setVariable('dump-all-selected', 'selected="selected"');
                break;
            case 'structure':
                $tpl->setVariable(
                    'dump-structure-selected', 'selected="selected"'
                );
                break;
            case 'content':
                $tpl->setVariable(
                    'dump-content-selected', 'selected="selected"'
                );
                break;
            }
        }
        break;
    case 'create':
        $tpl->setVariable('create-selected', 'checked="checked"');
        break;
    case 'update':
        $tpl->setVariable('update-selected', 'checked="checked"');
        break;
    case 'initialize':
        $tpl->setVariable('initialize-selected', 'checked="checked"');
        break;
    }
}

/**
 * Loads last error from cookies
 */
if (isset($_COOKIE['error']) && $_COOKIE['error']) {
    setcookie('error', '');
    $tpl->setCurrentBlock('error');
    $tpl->setVariable('error', $_COOKIE['error']);
    $tpl->parseCurrentBlock('error');
}

/**
 * Database drivers
 */
$databases = array(
    'mysql'  => 'MySQL',
    'mysqli' => 'MySQLi',
    'pgsql'  => 'PostGreSQL',
    'sqlite' => 'SQLite'
);
foreach ($databases as $key => $name) {
    $tpl->setCurrentBlock('databases');
    $tpl->setVariable('key', $key);
    $tpl->setVariable('name', $name);
    if (isset($_REQUEST['type']) && $_REQUEST['type'] == $key) {
        $tpl->setVariable('selected', 'selected="selected"');
    }
    $tpl->parseCurrentBlock('databases');
}

$tpl->show();
