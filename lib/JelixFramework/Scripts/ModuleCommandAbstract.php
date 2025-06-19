<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     MIT
 */

namespace Jelix\Scripts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ModuleCommandAbstract extends Command
{
    /** @var OutputInterface */
    protected $output;

    public function __construct()
    {
        parent::__construct();
    }
}
