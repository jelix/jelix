<?php
use \Jelix\JelixModule\Command;

$application->add(new Command\OpenApp());
$application->add(new Command\CloseApp());
$application->add(new Command\ClearTemp());
$application->add(new Command\FilesRights());
