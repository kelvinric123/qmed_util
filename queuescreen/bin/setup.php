<?php

use Rasque\Commands\SetupScreenCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../sources/vendor/autoload.php';

$console = new Application();

$console->add($command = new SetupScreenCommand(__DIR__ . '/../config.json'));

$console->setDefaultCommand($command->getName(), true);

$console->run();