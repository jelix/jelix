<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\Acl2Db\Command\Acl2;

use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractAcl2Cmd extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function _getGrpId(InputInterface $input, $onlypublic = false)
    {
        $param = $input->getArgument('group');
        if ($param == '__anonymous') {
            return $param;
        }

        if ($onlypublic) {
            $c = ' grouptype <2 AND ';
        } else {
            $c = '';
        }

        $cnx = \jDb::getConnection('jacl2_profile');
        $sql = 'SELECT id_aclgrp FROM '.$cnx->prefixTable('jacl2_group')." WHERE {$c} ";
        $sql .= ' id_aclgrp = '.$cnx->quote($param);
        $rs = $cnx->query($sql);
        if ($rec = $rs->fetch()) {
            return $rec->id_aclgrp;
        }

        throw new \Exception("this group doesn't exist or is private");
    }
}
