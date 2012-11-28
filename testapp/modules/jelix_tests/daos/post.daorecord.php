<?php 

abstract class postDaoRecord extends jDaoRecordBase {
    
    function getAuthorObject() {
        if ($this->author) {
            return jAuth::getUser($this->author);
        } else {
            return NULL;
        }
    }

}
