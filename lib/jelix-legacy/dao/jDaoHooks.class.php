<?php

/**
 * @package    jelix
 * @subpackage dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2021-2025 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Dao\DaoConditions;
use Jelix\Dao\DaoRecordInterface;
use Jelix\Event\Event;

/**
 * Hook on JelixDao factory
 */
class jDaoHooks implements \Jelix\Dao\DaoHookInterface
{
    /**
     * call before and after an insert
     *
     * @param string $daoName the dao file descriptor
     * @param DaoRecordInterface $record the record to insert
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @return void
     */
    public function onInsert(string $daoName, DaoRecordInterface $record, $when)
    {
        if ($when == self::EVENT_BEFORE) {
            Event::notify("daoInsertBefore", array('dao'=>$daoName, 'record'=>$record));
        }
        else {
            Event::notify("daoInsertAfter", array('dao'=>$daoName, 'record'=>$record));
        }
    }

    /**
     * call before and after an update
     *
     * @param string $daoName the dao file descriptor
     * @param DaoRecordInterface $record the record to update
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @return void
     */
    public function onUpdate(string $daoName, DaoRecordInterface $record, $when)
    {
        if ($when == self::EVENT_BEFORE) {
            Event::notify("daoUpdateBefore", array('dao'=>$daoName, 'record'=>$record));
        }
        else {
            Event::notify("daoUpdateAfter", array('dao'=>$daoName, 'record'=>$record));
        }
    }

    /**
     * call before and after a delete
     *
     * @param string $daoName the dao file descriptor
     * @param array $keys the key of the record to delete
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @param int|null|false $result the number of affected rows. False if the query has failed.
     * @return void
     */
    public function onDelete(string $daoName, $keys, $when, $result = null)
    {
        if ($when == self::EVENT_BEFORE) {
            Event::notify('daoDeleteBefore', array('dao' => $daoName, 'keys' => $keys));
        }
        else {
            Event::notify('daoDeleteAfter', array('dao' => $daoName, 'keys' => $keys, 'result' => $result));
        }
    }

    /**
     * call before and after a delete
     *
     * @param string $daoName the dao file descriptor
     * @param DaoConditions $searchCond the conditions to delete records
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     * @param int|null|false $result the number of affected rows. False if the query has failed.
     * @return void
     */
    public function onDeleteBy(string $daoName, DaoConditions $searchCond, $when, $result = null)
    {
        if ($when == self::EVENT_BEFORE) {
            Event::notify('daoDeleteByBefore', array('dao' => $daoName, 'criterias' => $searchCond));
        }
        else {
            Event::notify('daoDeleteByAfter', array('dao' => $daoName, 'criterias' => $searchCond, 'result' => $result));
        }
    }


    /**
     * @param string $daoName
     * @param string $methodName
     * @param string $methodType  the type of the method : 'update' or 'delete'
     * @param array  $parameters
     * @param int $when DaoHookInterface::EVENT_BEFORE or DaoHookInterface::EVENT_AFTER
     *
     * @return void
     */
    public function onCustomMethod(string $daoName, string $methodName, string $methodType, $parameters, $when)
    {
        $methname = ($methodType == 'update' ? 'Update' : 'Delete');
        if ($when == self::EVENT_BEFORE) {
            Event::notify("daoSpecific'.$methname.'Before", array(
                'dao'=>$daoName,
                'method'=> $methodName,
                'params' => $parameters
            ));
        }
        else {
            Event::notify("daoSpecific'.$methname.'After", array(
                'dao'=>$daoName,
                'method'=> $methodName,
                'params' => $parameters
            ));
        }
    }
}
