<?php 
namespace  JelixTests\Tests\Dao;

require_once(__DIR__.'/../../daos/post.daorecord.php');

abstract class PostBlog extends \postDaoRecord {
    
    function publish() {
        $this->status = 'published';
        $this->save();
    }
    
    function unpublish() {
        $this->status = NULL;
        $this->save();
    }

}
