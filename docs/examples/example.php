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
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * MDB2 reverse engineering of xml schemas script.
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */

?>
<html>
<body>
<?php
@include_once 'Var_Dump.php';
if (class_exists('Var_Dump')) {
    $var_dump = array('Var_Dump', 'display');
} else {
    $var_dump = 'var_dump';
}

$databases = array(
    'mysql'  => 'MySQL',
    'mysqli' => 'MySQLi',
    'pgsql'  => 'PostGreSQL',
    'sqlite' => 'SQLite',
);

if (isset($_GET['submit']) && $_GET['file'] != '') {
    require_once 'MDB2/Schema.php';
    $dsn = $_GET['type'].'://'.$_GET['user'].':'.$_GET['pass'].'@'.$_GET['host'].'/'.$_GET['name'];

    $schema =& MDB2_Schema::factory($dsn, array('debug' => true, 'log_line_break' => '<br>'));
    if (PEAR::isError($schema)) {
        $error = $schema->getMessage() . ' ' . $schema->getUserInfo();
    } else {
        if ($_GET['action']) {
            set_time_limit(0);
        }
        if ($_GET['action'] == 'dump') {
            switch ($_GET['dump']) {
            case 'structure':
                $dump_what = MDB2_SCHEMA_DUMP_STRUCTURE;
                break;
            case 'content':
                $dump_what = MDB2_SCHEMA_DUMP_CONTENT;
                break;
            default:
                $dump_what = MDB2_SCHEMA_DUMP_ALL;
                break;
            }
            $dump_config = array(
                'output_mode' => 'file',
                'output' => $_GET['file']
            );
            $operation = $schema->dumpDatabase($dump_config, $dump_what);
            call_user_func($var_dump, $operation);
        } elseif ($_GET['action'] == 'create') {
            $operation = $schema->updateDatabase($_GET['file'], 'old_'.$_GET['file']);
            if (PEAR::isError($operation)) {
                echo $operation->getMessage() . ' ' . $operation->getUserInfo();
                call_user_func($var_dump, $operation);
            } else {
                call_user_func($var_dump, $operation);
            }
        } else {
            $error = 'no action selected';
        }
        $warnings = $schema->getWarnings();
        if (count($warnings) > 0) {
            echo('Warnings<br>');
            call_user_func($var_dump, $operation);
        }
        if ($schema->db->getOption('debug')) {
            echo('Debug messages<br>');
            echo($schema->db->debugOutput().'<br>');
        }
        echo('Database structure<br>');
        call_user_func($var_dump, $operation);
        $schema->disconnect();
    }
}

if (!isset($_GET['submit']) || isset($error)) {
    if (isset($error) && $error) {
        echo($error.'<br>');
    }
?>
    <form action="" method="get">
    Database Type:
    <select name="type">
    <?php
        foreach ($databases as $key => $name) {
            echo '     <option value="' . $key . '"';
            if (isset($_GET['type']) && $_GET['type'] == $key) {
                echo ' selected="selected"';
            }
            echo '>' . $name . '</option>' . "\n";
        }
        ?>
    </select>
    <br />
    Username:
    <input type="text" name="user" value="<?php (isset($_GET['user']) ? $_GET['user'] : '') ?>" />
    <br />
    Password:
    <input type="text" name="pass" value="<?php (isset($_GET['pass']) ? $_GET['pass'] : '') ?>" />
    <br />
    Host:
    <input type="text" name="host" value="<?php (isset($_GET['host']) ? $_GET['host'] : '') ?>" />
    <br />
    Databasename:
    <input type="text" name="name" value="<?php (isset($_GET['name']) ? $_GET['name'] : '') ?>" />
    <br />
    Filename:
    <input type="text" name="file" value="<?php (isset($_GET['file']) ? $_GET['file'] : '') ?>" />
    <br />
    Dump:
    <input type="radio" name="action" value="dump" />
    <select name="dump">
        <option value="all"<?php if (isset($_GET['dump']) && $_GET['dump'] == 'all') {echo (' selected="selected"');} ?>>All</option>
        <option value="structure"<?php if (isset($_GET['dump']) && $_GET['dump'] == 'structure') {echo (' selected="selected"');} ?>>Structure</option>
        <option value="content"<?php if (isset($_GET['dump']) && $_GET['dump'] == 'content') {echo (' selected="selected"');} ?>>Content</option>
    </select>
    <br />
    Create:
    <input type="radio" name="action" value="create" />
    <br />
    <input type="submit" name="submit" value="ok" />
<?php } ?>
</form>
</body>
</html>
