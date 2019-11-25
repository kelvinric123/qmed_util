<?php

namespace Rasque\Commands;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class SetupScreenCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('rasque');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Which clinic are you setting this Raspberry up for? : ', false);

        if (($search = $helper->ask($input, $output, $question)) === false)
            return $this->execute($input, $output);

        $baseUrl = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        $result = @file_get_contents($baseUrl . '/api/installation/search?name=' . urlencode($search));

        if (!$result)
            return $this->write($output, 'Ooops, can\'t retrieve api at this moment');

        // search the clinic through APIs
        $result = json_decode($result, true);

        if (count($result) == 0)
            return $this->write($output, 'Oops, couldn\'t find any clinics');

        if (count($result) > 1) {
            $choice = [];

            foreach ($result as $index => $item) {
                $choice[$index + 1] = $item['name'] . $item['installation_id'];
            }

            $question = new ChoiceQuestion('We found more than one match, please select which clinic', $choice);

            $answer = $helper->ask($input, $output, $question);

            $record = $result[array_flip($choice)[$answer] - 1];
        } else {
            $record = $result[0];
        }

        if (!$helper->ask($input, $output, new ConfirmationQuestion('Set-up this raspberry for [' . $record['name'] . '](y/n)? : ')))
            return $this->write($output, 'Set-up cancelled');

        if (!trim(($deviceName = $helper->ask($input, $output, new Question('What should we label this queuescreen with? : ')))))
            return $this->write($output, 'Please write at least something..');

        $this->config['device_name'] = $deviceName;
        $this->config['installation_id'] = $record['installation_id'];

        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));

        $output->writeLn('Successfully installed!');
        $output->writeLn('');
        $output->writeln('Clinic : ' . $record['name']);
        $output->writeln('Device Name : ' . $deviceName);
        $output->writeln('Installation ID : ' . $record['installation_id']);
        
        // import autostart
        if (!file_exists('/home/pi/.config/autostart/chrome-queuescreen.desktop'))
            copy($this->basePath . '/autostart/chrome-queuescreen.desktop', '/home/pi/.config/autostart/chrome-queuescreen.desktop');

        return 1;
    }


}
