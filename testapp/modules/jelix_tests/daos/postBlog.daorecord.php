<?php 

require_once(__DIR__.'/post.daorecord.php');
abstract class postBlogDaoRecord extends postDaoRecord {
    
    function publish() {
        $this->status = 'published';
        $this->save();
    }
    
    function unpublish() {
        $this->status = NULL;
        $this->save();
    }

}
