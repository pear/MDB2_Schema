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
 * @author   Paul Cooper <pgc@ucecom.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  SVN: $Id$
 * @link     http://pear.php.net/packages/MDB2_Schema
 */

require_once dirname(__FILE__) . '/MDB2SchemaAbstract.php';

class MDB2SchemaTest extends MDB2SchemaAbstract {
    /**
     * @dataProvider provider
     */
    public function testCreateDatabase($fp) {
        $this->manualSetUp($fp);

        $action = 'updateDatabase';
    	if (!$this->methodExists($this->schema, $action)) {
            $this->markTestSkipped("Lacks $action method");
        }
        $result = $this->schema->updateDatabase(
            $this->driver_input_file,
            false,
            array('create' => '1', 'name' => $this->database)
        );
        if (PEAR::isError($result)) {
            $result = $this->schema->updateDatabase(
                $this->driver_input_file,
                false,
                array('create' => '0', 'name' => $this->database)
            );
        }
        if (!PEAR::isError($result)) {
            $result = $this->schema->updateDatabase(
                $this->lob_input_file,
                false,
                array('create' => '0', 'name' => $this->database)
            );
        }
        $this->checkResultForErrors($result, $action);
    }

    /**
     * @dataProvider provider
     */
    public function testUpdateDatabase($fp) {
        $this->manualSetUp($fp);

        $action = 'updateDatabase';
    	if (!$this->methodExists($this->schema, $action)) {
            $this->markTestSkipped("Lacks $action method");
        }

        $backup_file = $this->driver_input_file.$this->backup_extension;
        if (!file_exists($backup_file)) {
            copy($this->driver_input_file, $backup_file);
        }
        $result = $this->schema->updateDatabase(
            $this->driver_input_file,
            $backup_file,
            array('create' =>'0', 'name' =>$this->database)
        );
        $this->checkResultForErrors($result, $action);

        $backup_file = $this->lob_input_file.$this->backup_extension;
        if (!file_exists($backup_file)) {
            copy($this->lob_input_file, $backup_file);
        }
        $result = $this->schema->updateDatabase(
            $this->lob_input_file,
            $backup_file,
            array('create' =>'0', 'name' => $this->database)
        );
        $this->checkResultForErrors($result, $action);
    }
    
    /**
     * @dataProvider provider
     */
    public function testDumpDatabase($fp) {
        $this->manualSetUp($fp);

        $action = 'getDefinitionFromDatabase';
    	if (!$this->methodExists($this->schema, $action)) {
            $this->markTestSkipped("Lacks $action method");
        }
    	$definition = $this->schema->getDefinitionFromDatabase();
        $this->checkResultForErrors($definition, $action);

        $action = 'dumpDatabase';
    	if (!$this->methodExists($this->schema, $action)) {
            $this->markTestSkipped("Lacks $action method");
        }
        $dump_file = $this->lob_input_file.'.'.$this->dsn['phptype'].$this->dump_extension;
	    $result = $this->schema->dumpDatabase(
            $definition, 
            array('output_mode' => 'file',
                  'output' => $dump_file,
                  'end_of_line' => "\n",
                  ),
            MDB2_SCHEMA_DUMP_ALL);
        @unlink($dump_file);
        $this->checkResultForErrors($result, $action);
    }
}

