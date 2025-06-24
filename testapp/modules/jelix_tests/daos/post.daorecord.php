<?php 

abstract class postDaoRecord extends \Jelix\Dao\AbstractDaoRecord {
    
    function getAuthorObject() {
        if ($this->author) {
            return jAuth::getUser($this->author);
        } else {
            return NULL;
        }
    }

}
