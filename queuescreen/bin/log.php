<?php

use Rasque\Commands\LoggerCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../sources/vendor/autoload.php';

$console = new Application();

$console->add($command = new LoggerCommand(__DIR__ . '/../'));

$console->setDefaultCommand($command->getName(), true);

$console->run();