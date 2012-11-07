<?php 

abstract class postDaoRecord extends cDaoUserRecord_testapp_Jx_posts_Jx {
    
    function getAuthorObject() {
        if ($this->author) {
            return jAuth::getUser($this->author);
        } else {
            return NULL;
        }
    }

}
