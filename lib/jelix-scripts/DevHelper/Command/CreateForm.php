<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2007-2016 Laurent Jouanneau, 2008 Loic Mathaud, 2009 Bastien Jaillot
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateForm extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('module:create-form')
            ->setDescription('Create a new jforms file, from a jdao file')
            ->setHelp('')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'Name of the module where to create the dao'
            )
            ->addArgument(
                'form',
                InputArgument::REQUIRED,
                'the name of the form'
            )
            ->addArgument(
                'dao',
                InputArgument::OPTIONAL,
                'selector of the dao on which the form will be based. If not given, the jforms file will be empty.'
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
                'use-comments',
                null,
                InputOption::VALUE_NONE,
                'it will use DAO\'s property comments like form\'s labels'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');
        $formName = $input->getArgument('form');
        $daoName = $input->getArgument('dao');
        $profileName = $input->getOption('profile');

        require_once JELIX_LIB_PATH.'dao/jDaoParser.class.php';

        $path = $this->getModulePath($module);

        $formdir = $path.'forms/';
        $this->createDir($formdir);

        $formfile = strtolower($formName).'.form.xml';

        if ($input->getOption('create-locales')) {
            $locale_content = '';
            $locale_base = $module.'~'.strtolower($formName).'.form.';

            $locale_filename_fr = 'locales/fr_FR/';
            $this->createDir($path.$locale_filename_fr);
            $locale_filename_fr .= strtolower($formName).'.UTF-8.properties';

            $locale_filename_en = 'locales/en_US/';
            $this->createDir($path.$locale_filename_en);
            $locale_filename_en .= strtolower($formName).'.UTF-8.properties';

            $submit = "\n\n<submit ref=\"_submit\">\n\t<label locale='".$locale_base."ok' />\n</submit>";
        } else {
            $submit = "\n\n<submit ref=\"_submit\">\n\t<label>ok</label>\n</submit>";
        }

        if ($daoName === null) {
            if ($input->getOption('create-locales')) {
                $locale_content = "form.ok=OK\n";
                $this->createFile($path.$locale_filename_fr, 'locales.tpl', array('content' => $locale_content), 'Locales file');
                $this->createFile($path.$locale_filename_en, 'locales.tpl', array('content' => $locale_content), 'Locales file');
            }
            $this->createFile($formdir.$formfile, 'module/form.xml.tpl', array('content' => '<!-- add control declaration here -->'.$submit), 'Form');

            return;
        }

        \jApp::pushCurrentModule($module);

        $tools = \jDb::getConnection($profileName)->tools();

        // we're going to parse the dao
        $selector = new \jSelectorDao($daoName, $profileName);

        $doc = new \DOMDocument();
        $daoPath = $selector->getPath();

        if (!$doc->load($daoPath)) {
            throw new \jException('jelix~daoxml.file.unknown', $daoPath);
        }

        if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'dao/1.0') {
            throw new \jException('jelix~daoxml.namespace.wrong', array($daoPath, $doc->namespaceURI));
        }

        $parser = new \jDaoParser($selector);
        $parser->parse(simplexml_import_dom($doc), $tools);

        // now we generate the form file
        $properties = $parser->GetProperties();
        $table = $parser->GetPrimaryTable();

        $content = '';

        foreach ($properties as $name => $property) {
            if (!$property->ofPrimaryTable) {
                continue;
            }
            if ($property->isPK && $property->autoIncrement) {
                continue;
            }

            $attr = '';
            if ($property->required) {
                $attr .= ' required="true"';
            }

            if ($property->defaultValue !== null) {
                $attr .= ' defaultvalue="'.htmlspecialchars($property->defaultValue).'"';
            }

            if ($property->maxlength !== null) {
                $attr .= ' maxlength="'.$property->maxlength.'"';
            }

            if ($property->minlength !== null) {
                $attr .= ' minlength="'.$property->minlength.'"';
            }

            $datatype = '';
            $tag = 'input';

            switch ($property->unifiedType) {
                case 'integer':
                case 'numeric':
                    $datatype = 'integer';

                    break;

                case 'datetime':
                    $datatype = 'datetime';

                    break;

                case 'time':
                    $datatype = 'time';

                    break;

                case 'date':
                    $datatype = 'date';

                    break;

                case 'double':
                case 'float':
                    $datatype = 'decimal';

                    break;

                case 'text':
                case 'blob':
                    $tag = 'textarea';

                    break;

                case 'boolean':
                    $tag = 'checkbox';

                    break;
            }
            if ($datatype != '') {
                $attr .= ' type="'.$datatype.'"';
            }

            // use database comment to create form's label
            if ($property->comment != '' && $input->getOption('use-comments')) {
                if ($input->getOption('create-locales')) {
                    // replace special chars by dot
                    $locale_content .= 'form.'.$name.'='.htmlspecialchars(utf8_decode($property->comment))."\n";
                    $content .= "\n\n<{$tag} ref=\"{$name}\"{$attr}>\n\t<label locale='".$locale_base.$name."' />\n</{$tag}>";
                } else {
                    // encoding special chars
                    $content .= "\n\n<{$tag} ref=\"{$name}\"{$attr}>\n\t<label>".htmlspecialchars($property->comment)."</label>\n</{$tag}>";
                }
            } else {
                if ($input->getOption('create-locales')) {
                    $locale_content .= 'form.'.$name.'='.ucwords(str_replace('_', ' ', $name))."\n";
                    $content .= "\n\n<{$tag} ref=\"{$name}\"{$attr}>\n\t<label locale='".$locale_base.$name."' />\n</{$tag}>";
                } else {
                    $content .= "\n\n<{$tag} ref=\"{$name}\"{$attr}>\n\t<label>".ucwords(str_replace('_', ' ', $name))."</label>\n</{$tag}>";
                }
            }
        }

        if ($input->getOption('create-locales')) {
            $locale_content .= "form.ok=OK\n";
            $this->createFile($path.$locale_filename_fr, 'module/locales.tpl', array('content' => $locale_content), 'Locales file');
            $this->createFile($path.$locale_filename_en, 'module/locales.tpl', array('content' => $locale_content), 'Locales file');
        }

        $this->createFile($formdir.$formfile, 'module/form.xml.tpl', array('content' => $content.$submit), 'Form file');
    }
}
