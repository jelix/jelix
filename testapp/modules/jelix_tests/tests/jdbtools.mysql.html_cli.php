<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

include_once (JELIX_LIB_PATH.'plugins/db/mysql/mysql.dbtools.php');

class testmysqlDbTools extends mysqlDbTools {
    public function testParseSQLScript($script) {
        return $this->parseSQLScript($script);
    }
}

class UTjDbToolsMysql extends jUnitTestCase {

    function testExecSqlSimpleScript(){
        $db = jDb::getConnection();
        $tools = new testmysqlDbTools($db);

        $this->assertEqual(array("SELECT toto"), $tools->testParseSQLScript("SELECT toto"));
        $this->assertEqual($tools->testParseSQLScript("SELECT toto 'sdsd''mpo;ipi';"), array("SELECT toto 'sdsd''mpo;ipi'") );
        $this->assertEqual($tools->testParseSQLScript("SELECT toto 'sdsd\'mpo;ipi';"), array("SELECT toto 'sdsd\'mpo;ipi'") );
        $this->assertEqual($tools->testParseSQLScript("SELECT toto /*'sdsd'*/'mpo;ipi';"), array("SELECT toto 'mpo;ipi'") );
        $this->assertEqual($tools->testParseSQLScript("SELECT toto /*'sdsd\npo'*/'mpo;ipi';"), array("SELECT toto 'mpo;ipi'") );
        $this->assertEqual($tools->testParseSQLScript('SELECT toto "sdsd""mpo;ipi";'), array('SELECT toto "sdsd""mpo;ipi"') );
        $this->assertEqual($tools->testParseSQLScript('SELECT toto "sdsd\"mpo;ipi";'), array('SELECT toto "sdsd\"mpo;ipi"') );
        $this->assertEqual($tools->testParseSQLScript('SELECT toto /*"sdsd"*/"mpo;ipi";'), array('SELECT toto "mpo;ipi"') );
        $this->assertEqual($tools->testParseSQLScript('SELECT toto /*"sdsd'."\n".'po"*/"mpo;ipi";'), array('SELECT toto "mpo;ipi"') );

        $this->assertEqual($tools->testParseSQLScript("SELECT ''; SELECT toto"), array("SELECT ''", 'SELECT toto'));
        $this->assertEqual($tools->testParseSQLScript('SELECT ""; SELECT toto'), array('SELECT ""', 'SELECT toto'));
        $this->assertEqual($tools->testParseSQLScript('SELECT ``; SELECT toto'), array('SELECT ``', 'SELECT toto'));
    }

    function testExecSqlComplexScript(){
        $db = jDb::getConnection();
        $tools = new testmysqlDbTools($db);

        $sql ="  #oiuou y jgj gj, ; io 
    ALTER TABLE `ticket` ADD `component_id` INT NOT NULL AFTER `component` ; #louou ; poip \"
-- uturutut tut ; gdgd ; 
DROP PROCEDURE IF EXISTS updateComponent; -- oiuoiu  poi ; pi ;
UPDATE ticket SET component_id = cid WHERE component = cname;";
        $result=array(
            "ALTER TABLE `ticket` ADD `component_id` INT NOT NULL AFTER `component`",
            "DROP PROCEDURE IF EXISTS updateComponent",
            "UPDATE ticket SET component_id = cid WHERE component = cname"
        );
        $this->assertEqual($tools->testParseSQLScript($sql), $result);


        $sql =" ALTER TABLE `ticket` ADD `component_id` INT NOT NULL AFTER `component` ;
DROP PROCEDURE IF EXISTS updateComponent;
DELIMITER #
CREATE PROCEDURE updateComponent()
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE cid INT;
DECLARE cname VARCHAR(50);
DECLARE compCur CURSOR FOR SELECT id, name FROM product_component;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN compCur;
REPEAT
  FETCH compCur INTO cid, cname;
  if NOT done THEN
    UPDATE ticket SET component_id = cid WHERE component = cname;
    UPDATE ticket_change SET oldintvalue = cid WHERE oldvalue = cid AND field='component';
    UPDATE ticket_change SET newintvalue = cid WHERE newvalue = cid AND field='component';
  END IF;
UNTIL done END REPEAT;
CLOSE compCur;
END #
DELIMITER ;

CALL updateComponent();";
        $result = array("ALTER TABLE `ticket` ADD `component_id` INT NOT NULL AFTER `component`",
                        "DROP PROCEDURE IF EXISTS updateComponent",
                        "CREATE PROCEDURE updateComponent()
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE cid INT;
DECLARE cname VARCHAR(50);
DECLARE compCur CURSOR FOR SELECT id, name FROM product_component;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN compCur;
REPEAT
  FETCH compCur INTO cid, cname;
  if NOT done THEN
    UPDATE ticket SET component_id = cid WHERE component = cname;
    UPDATE ticket_change SET oldintvalue = cid WHERE oldvalue = cid AND field='component';
    UPDATE ticket_change SET newintvalue = cid WHERE newvalue = cid AND field='component';
  END IF;
UNTIL done END REPEAT;
CLOSE compCur;
END", "CALL updateComponent()");
       $this->assertEqual($tools->testParseSQLScript($sql), $result);


//echo '<pre>';var_export($tools->testParseSQLScript($sql));echo '</pre>';

    }
    
    function testTableList() {
        $db = jDb::getConnection();
        $tools = new testmysqlDbTools($db);
        
        $goodList = array('jacl_group', 'jacl_right_values', 'jacl_right_values_group',
                          'jacl_rights', 'jacl_subject', 'jacl_user_group',
                          'jacl2_group','jacl2_user_group','jacl2_subject',
                          'jacl2_rights', 'jlx_user', 'myconfig', 'product_test',
                          'product_tags_test', 'labels_test', 'products', 'jlx_cache','jsessions');
        
        $list = $tools->getTableList();
        sort($goodList);
        sort($list);
        $this->assertEqual($list, $goodList);
    }
}

