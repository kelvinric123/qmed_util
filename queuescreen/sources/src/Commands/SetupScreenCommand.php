<?php

namespace Rasque\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rasque\DeviceInfo;
use Rasque\Logger;
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

        $deviceId = DeviceInfo::create()->getDeviceId();


        // check if this raspberry already linked
        $installationId = null;
        try {
            $lookup = json_decode($this->http->request('GET', '/apis/installations/screen-lookup?device_id=' . $this->deviceId)->getBody(), true)['data'];

            $installationId = $lookup['installation_id'];
        } catch (ClientException $e) {
        }

        if ($installationId) {
            $clinic = json_decode($this->http->request('GET', '/apis/installations/' . $installationId)->getBody(), true)['data'];

            $this->envSetup();

            return $this->write($output, 'This raspberry has already been configured for clinic [' . $clinic['name'] . ']');
        }

        $question = new Question('Which clinic are you setting this Raspberry up for? search keyword : ', false);

        if (($search = $helper->ask($input, $output, $question)) === false)
            return $this->execute($input, $output);

        $clinics = json_decode($this->http->request('GET', '/apis/installations/search?name=' . urlencode($search))->getBody(), true)['data'];

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

        $this->createNewScreen($record['installation_id']);

        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));

        $output->writeLn('Successfully installed!');
        $output->writeLn('');
        $output->writeln('Clinic : ' . $record['name']);
        $output->writeln('Device ID: ' . $deviceId);
        $output->writeln('Installation ID : ' . $record['installation_id']);
        
        $this->envSetup();

        return 1;
    }
    
    protected function envSetup()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // import autostart, and create screen.sh
            $this->setupAutostart();

            // import cron
            $this->setupCron();
            
            // import www folder
//            $this->setupWWW();
            
            $this->setupShortcut();
            
            // log the setup
            Logger::instance()->log('setup');
        }
    }
    
    protected function setupShortcut()
    {
        $stub = file_get_contents($this->basePath . '/stubs/setup.desktop.stub');
        
        file_put_contents('/home/pi/Desktop/setup.desktop', str_replace('BIN_PATH', $this->basePath . '/bin', $stub));
    }
    
    /*protected function setupWWW()
    {
        $stub = file_get_contents($this->basePath . '/stubs/resource.php.stub');
        
//        $wwwPath = \Rasque\Config::instance()->get('www_dir', '/var/www/html');
        $wwwPath = $this->basePath . '/www';
        
        file_put_contents($wwwPath . '/dev/resource.php', str_replace('BASE_PATH', $this->basePath, $stub));
        
        $status = @file_put_contents($wwwPath . '/something.txt', 'test');
        
        if (!$status)
            exit('oops the public folder is permission is accessible by user pi');
            
        unlink($wwwPath . '/something.txt');
    }*/

    protected function setupAutostart()
    {
        // autostart
        $binPath = realpath($this->basePath . '/bin');

        $content = str_replace('BIN_PATH', $binPath, file_get_contents($this->basePath . '/stubs/chrome-queuescreen.desktop.stub'));

        file_put_contents('/home/pi/.config/autostart/chrome-queuescreen.desktop', $content);

        // screen.sh
        $stub = file_get_contents($this->basePath . '/stubs/screen.sh.stub');

        file_put_contents($binPath . '/screen.sh', str_replace('BIN_PATH', $binPath, $stub));
        
        $stub = file_get_contents($this->basePath . '/stubs/setup.sh.stub');
        
        file_put_contents($this->basePath . '/bin/setup.sh', str_replace('BIN_PATH', $binPath, $stub));
    }

    protected function setupCron()
    {
        $contents = file_get_contents($this->basePath . '/stubs/crontab.stub');
        
        file_put_contents($tmpPath = $this->basePath . '/tmp/crontab.stub', str_replace('BIN_PATH', $this->basePath . '/bin', $contents));

        shell_exec('crontab ' . $tmpPath);
        
        unlink($tmpPath);
    }

    protected function createNewScreen($installationId)
    {
        $screen = json_decode($this->http->request('POST', '/apis/installations/' . $installationId. '/screens', [
            'json' => ['device_id' => $this->deviceId]
        ])->getBody(), true)['data'];

        return $screen;

    }
}
