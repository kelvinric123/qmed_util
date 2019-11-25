<?php

use Rasque\Commands\GetSettingCommand;
use Rasque\Commands\SetupScreenCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../sources/vendor/autoload.php';

$console = new Application();

$console->add($command = new GetSettingCommand(__DIR__ . '/../'));

$console->setDefaultCommand($command->getName(), true);

$console->run();