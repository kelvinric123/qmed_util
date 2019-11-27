<?php

namespace Rasque\Commands;

use Rasque\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class LoggerCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('log');

        $this->addArgument('type', InputArgument::REQUIRED, 'started, ping, or setup');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        Logger::create()->log($input->getArgument('type'), []);

        return 0;
    }
}