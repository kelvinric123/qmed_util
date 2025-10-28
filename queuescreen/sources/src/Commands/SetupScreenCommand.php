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

        // Ask for player type selection
        $playerChoice = new ChoiceQuestion(
            'Select the video player type:',
            [
                1 => 'OMX Player (Traditional - overlays on webpage with manual positioning)',
                2 => 'Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)'
            ],
            1 // default to OMX
        );
        $playerChoice->setErrorMessage('Player type %s is invalid.');
        
        $selectedPlayer = $helper->ask($input, $output, $playerChoice);
        $playerType = ($selectedPlayer === 'Live TV2 (HTML5 Player - embedded in webpage, no OMX overlay)') ? 'live-tv2' : 'omx';

        $this->createNewScreen($record['installation_id']);
        
        // Store player type in config
        $this->config['player_type'] = $playerType;

        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));

        $output->writeLn('Successfully installed!');
        $output->writeLn('');
        $output->writeln('Clinic : ' . $record['name']);
        $output->writeln('Device ID: ' . $deviceId);
        $output->writeln('Installation ID : ' . $record['installation_id']);
        $output->writeln('Player Type: ' . ($playerType === 'live-tv2' ? 'Live TV2 (HTML5)' : 'OMX Player'));
        
        $this->envSetup();
        
        // If Live TV2 is selected, install dependencies and extract URL
        if ($playerType === 'live-tv2' && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $output->writeln('');
            $output->writeln('===========================================');
            $output->writeln('Setting up Live TV2 dependencies...');
            $output->writeln('===========================================');
            
            if (!$this->setupLiveTV2Dependencies($output)) {
                $output->writeln('<error>Warning: Failed to complete Live TV2 setup. Please run manually:</error>');
                $output->writeln('  pip3 install yt-dlp');
                $output->writeln('  sh ' . $this->basePath . '/bin/extract-tv2-url.sh');
                return 1;
            }
            
            $output->writeln('');
            $output->writeln('✅ Live TV2 setup complete!');
            $output->writeln('');
            $output->writeln('The system will now restart to show the TV2 stream.');
            $output->writeln('Rebooting in 5 seconds...');
            
            sleep(5);
            shell_exec('sudo reboot');
        }

        return 1;
    }
    
    protected function envSetup()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // import autostart, and create screen.sh
            $this->setupAutostart();

            // import LXDE setting
            $this->setupLXDE();

            // import cron
            $this->setupCron();
            
            // import www folder
//            $this->setupWWW();
            
            $this->setupShortcut();

            // log the setup
            Logger::instance()->log('setup');
        }
    }

    protected function setupLXDE()
    {
        $stub = file_get_contents($this->basePath . '/stubs/lxde-autostart.stub');

        if (!file_exists('/home/pi/.config/lxsession/LXDE-pi'))
            mkdir('/home/pi/.config/lxsession/LXDE-pi', 0755, true);

        file_put_contents('/home/pi/.config/lxsession/LXDE-pi/autostart', $stub);
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
    
    protected function setupLiveTV2Dependencies(OutputInterface $output)
    {
        // Step 1: Check if yt-dlp is installed
        $output->writeln('');
        $output->writeln('Step 1: Checking for yt-dlp...');
        
        $ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');
        
        if (empty($ytdlpCheck)) {
            $output->writeln('  yt-dlp not found. Installing...');
            
            // Try pip3 first
            $output->writeln('  Running: pip3 install yt-dlp');
            $result = shell_exec('pip3 install yt-dlp 2>&1');
            
            // Verify installation
            $ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');
            
            if (empty($ytdlpCheck)) {
                // Try with sudo if first attempt failed
                $output->writeln('  Trying with sudo...');
                shell_exec('sudo pip3 install yt-dlp 2>&1');
                
                $ytdlpCheck = shell_exec('command -v yt-dlp 2>/dev/null');
                
                if (empty($ytdlpCheck)) {
                    $output->writeln('  <error>❌ Failed to install yt-dlp</error>');
                    return false;
                }
            }
            
            $output->writeln('  ✅ yt-dlp installed successfully');
        } else {
            $output->writeln('  ✅ yt-dlp already installed');
        }
        
        // Step 2: Extract TV2 stream URL
        $output->writeln('');
        $output->writeln('Step 2: Extracting RTM TV2 stream URL...');
        $output->writeln('  This may take a moment... Trying multiple methods...');
        
        // Try the automatic extractor which tries multiple methods
        $autoExtractScript = $this->basePath . '/extract-url-auto.sh';
        $extractScript = $this->basePath . '/bin/extract-tv2-url.sh';
        
        // Prefer the auto extractor if available
        if (file_exists($autoExtractScript)) {
            shell_exec('chmod +x ' . $autoExtractScript);
            $result = shell_exec('sh ' . $autoExtractScript . ' 2>&1');
        } elseif (file_exists($extractScript)) {
            shell_exec('chmod +x ' . $extractScript);
            $result = shell_exec('sh ' . $extractScript . ' 2>&1');
        } else {
            $output->writeln('  <error>❌ Extraction scripts not found</error>');
            return false;
        }
        
        // Check if URL was successfully extracted
        $cacheFile = $this->basePath . '/www/dev/tv2-stream-url.txt';
        
        if (!file_exists($cacheFile) || filesize($cacheFile) < 10) {
            $output->writeln('  <error>❌ Failed to extract stream URL</error>');
            $output->writeln('');
            $output->writeln('  You can try manual extraction:');
            $output->writeln('  1. Open: https://rtmklik.rtm.gov.my/live/tv2');
            $output->writeln('  2. Press F12 -> Network tab -> Filter "m3u8"');
            $output->writeln('  3. Play video and copy the .m3u8 URL');
            $output->writeln('  4. Save to: ' . $cacheFile);
            return false;
        }
        
        $streamUrl = trim(file_get_contents($cacheFile));
        $output->writeln('  ✅ Stream URL extracted successfully');
        $output->writeln('  URL: ' . substr($streamUrl, 0, 60) . '...');
        
        return true;
    }
}
