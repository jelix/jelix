<?php
require_once(__DIR__.'/Command/HelloWorldCommand.php');

$application->add(new \Testapp\Command\HelloWorldCommand());

