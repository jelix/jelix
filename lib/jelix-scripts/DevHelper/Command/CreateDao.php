<?php
/**
 * @package     jelix-scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Nicolas Jeudy (patch ticket #99)
 * @contributor Gwendal Jouannic (patch ticket #615)
 * @contributor Loic Mathaud
 *
 * @copyright   2005-2016 Laurent Jouanneau
 * @copyright   2007 Nicolas Jeudy, 2008 Gwendal Jouannic, 2008 Loic Mathaud
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDao extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-dao')
            ->setDescription('Create a new dao file')
            ->setHelp('If the table name is not provided, the DAO name will be used as table name. You must provide a table name to indicate a sequence.')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module where to create the dao'
            )
            ->addArgument(
                'daoname',
                InputArgument::REQUIRED,
                'the name of the dao to create'
            )
            ->addArgument(
                'table',
                InputArgument::OPTIONAL,
                'name of the main table on which the dao is mapped. You cannot indicate multiple tables'
            )
            ->addArgument(
                'sequence',
                InputArgument::OPTIONAL,
                'name of the sequence used to auto increment the primary key'
            )
            ->addOption(
                'profile',
                null,
                InputOption::VALUE_REQUIRED,
                'indicate the name of the profile to use for the database connection',
                ''
            )
            ->addOption(
                'empty',
                null,
                InputOption::VALUE_NONE,
                'just create an empty dao file (it doesn\'t connect to the database)'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $daoname = $input->getArgument('daoname');

        $path = $this->getModulePath($module);

        $filename = $path.'daos/';
        $this->createDir($filename);

        $daofile = strtolower($daoname).'.dao.xml';
        $filename .= $daofile;

        $profile = $input->getOption('profile');

        $param = array('name' => $daoname,
            'table' => $input->getArgument('table'), );
        if ($param['table'] == null) {
            $param['table'] = $param['name'];
        }

        if ($input->getOption('empty')) {
            $this->createFile($filename, 'module/dao_empty.xml.tpl', $param, 'Empty DAO');
        } else {
            $sequence = $input->getArgument('sequence');
            $tools = \jDb::getConnection($profile)->tools();
            $fields = $tools->getFieldList($param['table'], $sequence);

            $properties = '';
            $primarykeys = '';

            if (empty($fields)) {
                throw new \Exception('The table '.$param['table'].' does not exist.');
            }

            foreach ($fields as $fieldname => $prop) {
                $name = str_replace('-', '_', $fieldname);
                $properties .= "\n        <property name=\"{$name}\" fieldname=\"{$fieldname}\"";
                $properties .= ' datatype="'.$prop->type.'"';
                if ($prop->primary) {
                    if ($primarykeys != '') {
                        $primarykeys .= ','.$fieldname;
                    } else {
                        $primarykeys .= $fieldname;
                    }
                }
                if ($prop->notNull && !$prop->autoIncrement) {
                    $properties .= ' required="true"';
                }

                if ($prop->autoIncrement) {
                    $properties .= ' autoincrement="true"';
                }

                if ($prop->hasDefault) {
                    $properties .= ' default="'.htmlspecialchars($prop->default).'"';
                }
                if ($prop->length) {
                    $properties .= ' maxlength="'.$prop->length.'"';
                }
                if ($prop->sequence) {
                    $properties .= ' sequence="'.$prop->sequence.'"';
                }
                // form generator use this feature
                if ($prop->comment) {
                    $properties .= ' comment="'.htmlspecialchars($prop->comment).'"';
                }
                $properties .= '/>';
            }

            if ($primarykeys == '') {
                throw new \Exception('The table '.$param['table'].' has no primary keys. A dao needs a primary key on the table to be defined.');
            }

            $param['properties'] = $properties;
            $param['primarykeys'] = $primarykeys;
            $this->createFile($filename, 'module/dao.xml.tpl', $param, 'DAO');
            return 0;
        }
    }
}
