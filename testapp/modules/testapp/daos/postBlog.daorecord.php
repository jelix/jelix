<?php 


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
