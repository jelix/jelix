<?php 


abstract class postTrackerDaoRecord extends postDaoRecord {
    
    function open() {
        $this->status = 'open';
        $this->save();
    }
    
    function close() {
        $this->status = 'closed';
        $this->save();
    }

}
