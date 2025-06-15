<?php
namespace  JelixTests\Tests\Dao;

use jDaoFactoryBase;

abstract class PostBlogFactory extends jDaoFactoryBase
{
    public function getByEmail($email)
    {
        $query = $this->_selectClause.$this->_fromClause.
            'WHERE email = :email LIMIT 0,1';
        $stmt = $this->_conn->prepare($query);
        $rs =  $stmt->execute(array('email' => $email));
        $this->finishInitResultSet($rs);
        return $rs->fetch();
    }
}
