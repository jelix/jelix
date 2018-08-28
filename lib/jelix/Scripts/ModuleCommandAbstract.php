<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @link        http://www.jelix.org
 * @licence     MIT
 */
namespace Jelix\Scripts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

abstract class ModuleCommandAbstract extends Command
{
    private $isVerbose = false;

    /** @var OutputInterface */
    protected $output = null;

    public function __construct()
    {
        parent::__construct();
    }

}
