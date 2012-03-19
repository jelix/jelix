<?php
/**
* @package    jelix
* @subpackage db_driver
* @author      Florian Lonqueu-Brochard
* @copyright  2012 Florian Lonqueu-Brochard
* @link      http://www.jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

require_once(dirname(__FILE__).'/mysqli.dbresultset.php');

class mysqliDbStatement extends jDbStatement {

    public function execute(){
        $this->_stmt->execute();

        if($this->_stmt->result_metadata()){
            //need the MySQL native driver (by default in php 5.3.0)
            try{
                $res = new mysqliDbResultSet($this->_stmt->get_result());
            }
            catch(Exception $e){
                throw new jException('jelix~db.error.nofunction', 'Mysqlnd');
            }
        }
        else{
            if($this->_stmt->affected_rows > 0) {
                $res = $this->_stmt->affected_rows;
            }
            elseif ($this->_stmt->affected_rows === null) {
                throw new jException('jelix~db.error.invalid.param');
            }
            else{
                throw new jException('jelix~db.error.query.bad', $this->_stmt->errno);
            }
        }
        return $res;
    }

    /**
     * @see http://www.php.net/manual/fr/mysqli-stmt.bind-param.php
     */
    public function bindParam(){
        $args = func_get_args();
        $method = new ReflectionMethod('mysqli_stmt', 'bind_param');
        $res = $method->invokeArgs($this->_stmt, $args); 
        if(!$res){
            throw new jException('jelix~db.error.invalid.param');
        }
        return $res;
    }


    protected function _free(){
        return $this->_stmt->close();
    }


    public function getAttribute($attr){
        return $this->_stmt->get_attr($attr);
    }

    public function setAttribute($attr, $value){
        return $this->_stmt->get_attr($attr, $value);
    }
}

