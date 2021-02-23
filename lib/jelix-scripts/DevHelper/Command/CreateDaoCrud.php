<?php
/**
 * @package     jelix-scripts
 *
 * @author      Laurent Jouanneau
 * @contributor Bastien Jaillot
 * @contributor Loic Mathaud
 * @contributor Mickael Fradin
 *
 * @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud, 2010 Mickael Fradin
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDaoCrud extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-dao-crud')
            ->setDescription('Create a new controller jControllerDaoCrud')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module'
            )
            ->addArgument(
                'table',
                InputArgument::REQUIRED,
                'name of the main table on which the dao is mapped'
            )
            ->addArgument(
                'ctrlname',
                InputArgument::OPTIONAL,
                'name of the controller'
            )
            ->addOption(
                'profile',
                null,
                InputOption::VALUE_REQUIRED,
                'indicate the name of the profile to use for the database connection',
                ''
            )
            ->addOption(
                'create-locales',
                null,
                InputOption::VALUE_NONE,
                'creates the locales file for labels of the form'
            )
            ->addOption(
                'acl2',
                null,
                InputOption::VALUE_NONE,
                'automatically generate ACL2 rights (list, view, create, modify, delete)'
            )
            ->addOption(
                'acl2-locale',
                null,
                InputOption::VALUE_REQUIRED,
                'indicates the selector prefix for the file storing the locales of rights, when -acl2 is set',
                ''
            )
            ->addOption(
                'masteradmin',
                null,
                InputOption::VALUE_NONE,
                'add an event listener to add a menu item in master_admin'
            )

        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $path = $this->getModulePath($module);

        $table = $input->getArgument('table');
        $ctrlname = $input->getArgument('ctrlname');
        if (!$ctrlname) {
            $ctrlname = $table;
        }

        if (file_exists($path.'controllers/'.$ctrlname.'.classic.php')) {
            throw new \Exception("controller '".$ctrlname."' already exists");
        }

        $arguments = array();
        if ($output->isVerbose()) {
            $arguments['-v'] = true;
        }

        // create the dao file
        $options = array('module' => $module,
            'daoname' => $table,
            'table' => $table, );
        $profile = $input->getOption('profile');
        if ($profile) {
            $options['--profile'] = $profile;
        }
        $options = array_merge($arguments, $options);
        $this->executeSubCommand('module:create-dao', $options, $output);

        // create the form file
        $options = array('module' => $module,
            'form' => $table,
            'dao' => $table,
        );
        if ($profile) {
            $options['--profile'] = $profile;
        }
        if ($input->getOption('create-locales')) {
            $options['--create-locales'] = true;
        }
        $options = array_merge($arguments, $options);
        $this->executeSubCommand('module:create-form', $options, $output);

        // set rights
        $acl2rights = '';
        $pluginsParameters = "
                '*'          =>array('auth.required'=>true),
                'index'      =>array('jacl2.right'=>'{$module}.{$ctrlname}.view'),
                'precreate'  =>array('jacl2.right'=>'{$module}.{$ctrlname}.create'),
                'create'     =>array('jacl2.right'=>'{$module}.{$ctrlname}.create'),
                'savecreate' =>array('jacl2.right'=>'{$module}.{$ctrlname}.create'),
                'preupdate'  =>array('jacl2.right'=>'{$module}.{$ctrlname}.update'),
                'editupdate' =>array('jacl2.right'=>'{$module}.{$ctrlname}.update'),
                'saveupdate' =>array('jacl2.right'=>'{$module}.{$ctrlname}.update'),
                'view'       =>array('jacl2.right'=>'{$module}.{$ctrlname}.view'),
                'delete'     =>array('jacl2.right'=>'{$module}.{$ctrlname}.delete')";
        $acl2 = $input->getOption('acl2');
        if ($acl2) {
            $subjects = array('view' => 'View',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete', );
            $sel = $input->getOption('acl2-locale');
            if (!$sel) {
                $sel = $module.'~acl'.$ctrlname;
            }

            foreach ($subjects as $subject => $label) {
                $subject = $module.'.'.$ctrlname.'.'.$subject;
                $labelkey = $sel.'.'.$subject;
                $options = array('right' => $subject,
                    'labelkey' => $labelkey,
                    'rightgroup' => 'null',
                    'rightlabel' => $label.' '.$ctrlname, );
                $options = array_merge($arguments, $options);
                $this->executeSubCommand(
                    'acl2:right-create',
                    $options,
                    $output
                );
            }
        } else {
            $pluginsParameters = '/*'.$pluginsParameters."\n*/";
        }

        // create the controller
        $this->createDir($path.'controllers/');
        $params = array('name' => $ctrlname,
            'module' => $module,
            'table' => $table,
            'profile' => $profile,
            'acl2rights' => $pluginsParameters, );

        $this->createFile(
            $path.'controllers/'.$ctrlname.'.classic.php',
            'module/controller.daocrud.tpl',
            $params,
            'Controller'
        );

        if ($input->getOption('masteradmin')) {
            // create a listener for master_admin
            if ($acl2) {
                $params['checkacl2'] = "if(jAcl2::check('{$module}.{$ctrlname}.view'))";
            } else {
                $params['checkacl2'] = '';
            }
            $this->createFile(
                $path.'classes/'.$ctrlname.'menu.listener.php',
                'module/masteradminmenu.listener.php.tpl',
                $params,
                'Listener'
            );
            if (file_exists($path.'events.xml')) {
                $xml = simplexml_load_file($path.'events.xml');
                $xml->registerXPathNamespace('j', 'http://jelix.org/ns/events/1.0');
                $listenerPath = "j:listener[@name='".$ctrlname."menu']";
                $eventPath = "j:event[@name='masteradminGetMenuContent']";
                if (!$event = $xml->xpath("//{$listenerPath}/{$eventPath}")) {
                    if ($listeners = $xml->xpath("//{$listenerPath}")) {
                        $listener = $listeners[0];
                    } else {
                        $listener = $xml->addChild('listener');
                        $listener->addAttribute('name', $ctrlname.'menu');
                    }
                    $event = $listener->addChild('event');
                    $event->addAttribute('name', 'masteradminGetMenuContent');
                    $result = $xml->asXML($path.'events.xml');
                    if ($this->verbose() && $result) {
                        $output->writeln("Events.xml in module '".$module."' has been updated.");
                    } elseif (!$result) {
                        $output->writeln("Warning: events.xml in module '".$module."' cannot be updated, check permissions or add the event manually.");
                    }
                } elseif ($this->verbose()) {
                    $output->writeln("events.xml in module '".$module."' is already updated.");
                }
            } else {
                $this->createFile(
                    $path.'events.xml',
                    'module/events_crud.xml.tpl',
                    array('classname' => $ctrlname.'menu')
                );
            }
        }

        // ------- setup urls configuration
        if (!file_exists($path.'urls.xml')) {
            $this->createFile($path.'urls.xml', 'module/urls.xml.tpl', array());
            if ($output->isVerbose()) {
                $output->writeln('Notice: you should link the urls.xml of the module '.$module."', into the app/system/urls.xml file.");
            }
        }

        $xml = simplexml_load_file($path.'urls.xml');
        $xml->registerXPathNamespace('j', 'http://jelix.org/ns/suburls/1.0');

        // if the url already exists, let's try an other
        $count = 0;
        $urlXPath = "//j:url[@pathinfo='/".$ctrlname."/']";
        while ($url = $xml->xpath("//{$urlXPath}")) {
            ++$count;
            $urlXPath = "//j:url[@pathinfo='/".$ctrlname.'-'.$count."/']";
        }

        if ($count == 0) {
            $urlPath = '/'.$ctrlname.'/';
        } else {
            $urlPath = '/'.$ctrlname.'-'.$count.'/';
        }

        /*
        <url pathinfo="/thedata/" action="mycrud:index" />
        <url pathinfo="/thedata/view/:id" action="mycrud:view" />
        <url pathinfo="/thedata/precreate" action="mycrud:precreate" />
        <url pathinfo="/thedata/create" action="mycrud:create" />
        <url pathinfo="/thedata/savecreate" action="mycrud:savecreate" />
        <url pathinfo="/thedata/preedit/:id" action="mycrud:preupdate" />
        <url pathinfo="/thedata/edit/:id" action="mycrud:editupdate" />
        <url pathinfo="/thedata/save/:id" action="mycrud:saveupdate" />
        <url pathinfo="/thedata/delete/:id" action="mycrud:delete" />
        */

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath);
        $url->addAttribute('action', $ctrlname.':index');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'view/:id');
        $url->addAttribute('action', $ctrlname.':view');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'precreate');
        $url->addAttribute('action', $ctrlname.':precreate');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'create');
        $url->addAttribute('action', $ctrlname.':create');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'savecreate');
        $url->addAttribute('action', $ctrlname.':savecreate');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'preedit/:id');
        $url->addAttribute('action', $ctrlname.':preupdate');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'edit/:id');
        $url->addAttribute('action', $ctrlname.':editupdate');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'save/:id');
        $url->addAttribute('action', $ctrlname.':saveupdate');

        $url = $xml->addChild('url');
        $url->addAttribute('pathinfo', $urlPath.'delete/:id');
        $url->addAttribute('action', $ctrlname.':delete');

        $result = $xml->asXML($path.'urls.xml');
        if ($output->isVerbose() && $result) {
            $output->writeln("urls.xml in module '".$module."' has been updated.");
        } elseif (!$result) {
            $output->writeln("Warning: urls.xml in module '".$module."' cannot be updated, check permissions or add the urls manually.");
        }
    }
}
