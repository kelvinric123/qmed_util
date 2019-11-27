<?php

namespace Rasque\Commands;

use GuzzleHttp\Client;
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

        $question = new Question('Which clinic are you setting this Raspberry up for? search keyword : ', false);

        if (($search = $helper->ask($input, $output, $question)) === false)
            return $this->execute($input, $output);

        $baseUrl = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        $http = new Client([
            'base_uri' => $baseUrl
        ]);

//        $result = @file_get_contents($baseUrl . '/apis/installations/search?name=' . urlencode($search));
        $clinics = json_decode($http->request('GET', '/apis/installations/search?name=' . urlencode($search))->getBody(), true)['data'];
//        if (!$result)
//            return $this->write($output, 'Ooops, can\'t retrieve api at this moment');

        // search the clinic through APIs
//        $result = json_decode($result, true);

        if (count($clinics) == 0)
            return $this->write($output, 'Oops, couldn\'t find any clinics');

        if (count($clinics) > 1) {
            $choice = [];

            foreach ($clinics as $index => $item) {
                $choice[$index + 1] = $item['name'] . $item['installation_id'];
            }

            $question = new ChoiceQuestion('We found more than one match, please select which clinic', $choice);

            $answer = $helper->ask($input, $output, $question);

            $record = $clinics[array_flip($choice)[$answer] - 1];
        } else {
            $record = $clinics[0];
        }

        if (!$helper->ask($input, $output, new ConfirmationQuestion('Set-up this raspberry for [' . $record['name'] . '](y/n)? : ')))
            return $this->write($output, 'Set-up cancelled');

        // search for existing screens
        $screens = json_decode($http->request('GET', '/apis/installations/' . $record['installation_id'] . '/screens')->getBody(), true)['data'];

        if (count($screens) > 0) {
            $choices = [];

            $choices[1] = 'Create new screen';

            foreach ($screens as $index => $screen) {
                $choices[$index + 2] = $screen['name'];
            }

            $question = new ChoiceQuestion('Oops, we already found existing screens', $choices);

            $answer = $helper->ask($input, $output, $question);

            if ($answer == 'Create new screen') {
                if (!($screen = $this->createNewScreen($helper, $input, $output, $http, $record['installation_id'])))
                    return 0;
            } else {
                $screen = $screens[array_flip($choices)[$answer] - 2];
            }
        } else {
            if (!($screen = $this->createNewScreen($helper, $input, $output, $http, $record['installation_id'])))
                return 0;
        }

        $this->config['screen_id'] = $screen['id'];
        $this->config['installation_id'] = $record['installation_id'];

        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));

        $output->writeLn('Successfully installed!');
        $output->writeLn('');
        $output->writeln('Clinic : ' . $record['name']);
        $output->writeln('Screen Name : ' . $screen['name']);
        $output->writeln('Installation ID : ' . $record['installation_id']);

        // import autostart, and create screen.sh
        $this->setupAutostart();



        return 1;
    }

    protected function setupAutostart()
    {
        // autostart
        $binPath = realpath($this->basePath . '/bin');

        $content = str_replace('BIN_PATH', $binPath, file_get_contents($this->basePath . '/autostart/chrome-queuescreen.desktop'));

        file_put_contents('/home/pi/.config/autostart/chrome-queuescreen.desktop', $content);

        // screen.sh
        $stub = file_get_contents($binPath . '/screen.sh.stub');

        file_put_contents($binPath . '/screen.sh', str_replace('BIN_PATH', $binPath, $stub));
    }

    protected function createNewScreen(QuestionHelper $helper, $input, $output, Client $http, $installationId)
    {
        $screenName = $helper->ask($input, $output, new Question('What should we label this queuescreen with? : '));

        if (!$screenName)
            return $this->createNewScreen($helper, $input, $output, $http, $installationId);

        $screen = json_decode($http->request('POST', '/apis/installations/' . $installationId . '/screens', [
            'json' => ['name' => $screenName]
        ])->getBody(), true)['data'];

        return $screen;

    }
}
