<?php

namespace Rasque\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class GetSettingCommand extends BaseCommand
{
    protected static $defaultName = 'settings';

    public function configure()
    {
        $this->addArgument('setting', InputArgument::REQUIRED, 'setting name : host, installation_id, device_name, screen_url');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $setting = $input->getArgument('setting');

        if (!in_array($setting, ['host', 'installation_id', 'device_name', 'screen_url']))
            return $this->write($output, 'Either host, installation_id, device_name, screen_url only');

        $baseUrl = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        if ($setting == 'screen_url')
            return $this->write($output, $baseUrl . '/queuescreen/' . $this->config['installation_id']);

        $this->write($output, $this->config[$setting]);
        
        return 0;
    }
}