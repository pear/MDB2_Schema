<!-- vim: se et ts=4 sw=4 sts=4 fdm=marker tw=80 ft=html: -->
<!--
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
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head><title>MDB2_Schema Web Frontend</title></head>
    <body>
        <!-- BEGIN error -->
        <h1>Error</h1>
        <div id="errors">
            <ul>
                <li>{error}</li>
            </ul>
        </div>
        <!-- END error -->
        <form method="get" action="action.php">
            <fieldset>
                <legend>Database information</legend>
                <table>
                    <tr>
                        <td><label for="type">Database Type:</label></td>
                        <td>
                            <select name="type" id="type">
                                <!-- BEGIN databases -->
                                <option value="{key}" {selected}>{name}</option>
                                <!-- END databases -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="user">Username:</label></td>
                        <td><input type="text" name="user" id="user" value="{username}" /></td>
                    </tr>
                    <tr>
                        <td><label for="pass">Password:</label></td>
                        <td><input type="text" name="pass" id="pass" value="{password}" /></td>
                    </tr>
                    <tr>
                        <td><label for="host">Host:</label></td>
                        <td><input type="text" name="host" id="host" value="{hostspec}" /></td>
                    </tr>
                    <tr>
                        <td><label for="name">Databasename:</label></td>
                        <td><input type="text" name="name" id="name" value="{database}" /></td>
                    </tr>
                    <tr>
                        <td><label for="char">Table Charset:</label></td>
                        <td><input type="text" name="char" id="char" value="{charset}" /></td>
                    </tr>
                    <tr>
                        <td><label for="file">Filename:</label></td>
                        <td><input type="text" name="file" id="file" value="{file}" /></td>
                    </tr>
                    <tr>
                        <td><label for="dump">Dump:</label></td>
                        <td>
                            <input type="radio" name="action" id="dump" value="dump" {dump-selected} />
                            <select id="dumptype" name="dumptype">
                                <option value="all" {dump-all-selected}>All</option>
                                <option value="structure" {dump-structure-selected}>Structure</option>
                                <option value="content" {dump-content-selected}>Content</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="create">Create:</label></td>
                        <td><input type="radio" name="action" id="create" value="create" {create-selected} /></td>
                    </tr>
                    <tr>
                        <td><label for="update">Update:</label></td>
                        <td><input type="radio" name="action" id="update" value="update" {update-selected} /></td>
                    </tr>
                    <tr>
                        <td><label for="update">Initialize:</label></td>
                        <td><input type="radio" name="action" id="initialize" value="initialize" {initialize-selected} /></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>
                <legend>Options</legend>
                <table>
                    <tr>
                        <td><label for="log_line_break">Log line break:</label></td>
                        <td><input type="text" name="log_line_break" id="log_line_break" value="{log_line_break}" /></td>
                    </tr>
                    <tr>
                        <td><label for="idxname_format">Index Name Format:</label></td>
                        <td><input type="text" name="idxname_format" id="idxname_format" value="{idxname_format}" /></td>
                    </tr>
                    <tr>
                        <td><label for="DBA_username">DBA_username:</label></td>
                        <td><input type="text" name="DBA_username" id="DBA_username" value="{DBA_username}" /></td>
                    </tr>
                    <tr>
                        <td><label for="DBA_password">DBA_password:</label></td>
                        <td><input type="text" name="DBA_password" id="DBA_password" value="{DBA_password}" /></td>
                    </tr>
                    <tr>
                        <td><label for="default_table_type">Default Table Type:</label></td>
                        <td><input type="text" name="default_table_type" id="default_table_type" value="{default_table_type}" /></td>
                    </tr>
                    <tr>
                        <td><label for="debug">Debug:</label></td>
                        <td><input type="checkbox" name="debug" id="debug" value="1" {debug} /></td>
                    </tr>
                    <tr>
                        <td><label for="use_transactions">Use Transactions:</label></td>
                        <td><input type="checkbox" name="use_transactions" id="use_transactions" value="1" {use_transactions} /></td>
                    </tr>
                    <tr>
                        <td><label for="quote_identifier">Quote Identifier:</label></td>
                        <td><input type="checkbox" name="quote_identifier" id="quote_identifier" value="1" {quote_identifier} /></td>
                    </tr>
                    <tr>
                        <td><label for="force_defaults">Force Defaults:</label></td>
                        <td><input type="checkbox" name="force_defaults" id="force_defaults" value="1" {force_defaults} /></td>
                    </tr>
                    <tr>
                        <td><label for="portability">Portability:</label></td>
                        <td><input type="checkbox" name="portability" id="portability" value="1" {portability} /></td>
                    </tr>
                    <tr>
                        <td><label for="show_structure">Show database structure:</label></td>
                        <td><input type="checkbox" name="show_structure" id="show_structure" value="1" {show_structure} /></td>
                    </tr>
                    <tr>
                        <td><label for="disable_query">Do not modify database:</label></td>
                        <td><input type="checkbox" name="disable_query" id="disable_query" value="1" {disable_query} /></td>
                    </tr>
                    <tr>
                        <td><label for="drop_obsolete_objects">Drop obsolete tables/seq:</label></td>
                        <td><input type="checkbox" name="drop_obsolete_objects" id="drop_obsolete_objects" value="1" {drop_obsolete_objects} /></td>
                    </tr>
                </table>
            </fieldset>

            <p><input type="submit" name="submit" value="ok" /><input type="button" value="reset" /></p>
        </form>
    </body>
</html>
