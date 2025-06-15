<?php
namespace  JelixTests\Tests\Dao;

use Jelix\Dao\AbstractDaoFactory;

abstract class PostBlogFactory extends AbstractDaoFactory
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
