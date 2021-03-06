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
 * Configures template engine
 */
$tpl = new HTML_Template_IT();
$tpl->loadTemplatefile('result.tpl', true, true);

$warnings = $schema->getWarnings();
if (count($warnings) > 0) {
    $tpl->setCurrentBlock('warnings');
    $tpl->setVariable('warnings', var_export($warnings, true));
    $tpl->parseCurrentBlock('warnings');
}

if ($schema->db->getOption('debug')) {
    $tpl->setCurrentBlock('debug');
    $tpl->setVariable('debug', $schema->db->getDebugOutput());
    $tpl->parseCurrentBlock('debug');
}

if ($data->show_structure
    && isset($definition)
    && is_array($definition)
) {
    $tpl->setCurrentBlock('show_structure');
    $tpl->setVariable('show_structure', var_export($definition, true));
    $tpl->parseCurrentBlock('show_structure');
}

/**
 * Loads last error from cookies
 */
if (isset($error) && $error) {
    $tpl->setCurrentBlock('error');
    $tpl->setVariable('error', $error);
    $tpl->parseCurrentBlock('error');
} else {
    $data->saveCookies();
}

$tpl->show();
