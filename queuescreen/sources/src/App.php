<?php

namespace Rasque;

class App
{
    protected static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if (!static::$instance)
            static::$instance = new static();

        return static::$instance;
    }

    public function getBasePath()
    {
        return realpath(__DIR__ . '/../..');
    }

    public function log($type, $params = [], array $guzzleOpts = [])
    {
        return Logger::instance()->log($type, $params, $guzzleOpts);
    }

    public function getVersion()
    {
//        shell_exec('cd ' . realpath($this->getBasePath() . '/..'));
        chdir(realpath($this->getBasePath() . '/..'));

        return trim(shell_exec('git show --format="%h" --no-patch'));
    }

    public function getPath($path)
    {
        return realpath(rtrim($this->getBasePath(), '/') . '/' . ltrim($path, '/'));
    }

    /**
     * Check if it currently hangs
     * @return bool
     */
    public function isBSOD()
    {
        return trim(shell_exec('dmesg | grep "blocked for more than 120"')) ? true : false;
    }

    public function reboot($reason)
    {
        $lastRebootTime = @file_get_contents($this->getBasePath() . '/last-reboot-time');

        if (!$lastRebootTime) {
            \Rasque\Logger::instance()->log($reason);
            file_put_contents($this->getPath('last-reboot-time'), time());
            shell_exec('sudo reboot');
            return;
        }

        // ONLY reboot when there's no reboot under the last 5 minutes
        if (time() > strtotime('+5 minutes', $lastRebootTime)) {
            \Rasque\Logger::instance()->log($reason);
            file_put_contents($this->getPath('last-reboot-time'), time());
            shell_exec('sudo reboot');
            return;
        }

        return;
    }

    public function getProcessIds($pattern)
    {
        $running = shell_exec('ps auxww | grep ' . $pattern);

        $ids = [];

        foreach (explode("\n", $running) as $line) {
            if (strpos($line, $pattern) === false)
                continue;

            if (strpos($line, 'bin/sh -c') !== false)
                continue;

            if (strpos($line, 'auxww') !== false)
                continue;

            if (strpos($line, 'grep') !== false)
                continue;

            $parts = preg_split('/\s+/', $line);

            $ids[]  = $parts[1];
        }

        return $ids;
    }

    public function killProcess($id)
    {
        shell_exec('kill ' . $id);
    }

    public function kill($pattern)
    {
        $processIds = $this->getProcessIds($pattern);

        if (!$processIds)
            return;

        foreach ($processIds as $id)
            $this->killProcess($id);
    }

    public function killAds()
    {
        $this->kill('play-ad.php');
        $this->kill('omxplayer');
    }

    public function processIsRunning($pattern)
    {
        $running = shell_exec("ps auxww | grep " . $pattern);

        $process = 0;

        foreach (explode("\n", $running) as $line) {
            if (strpos($line, $pattern) === false)
                continue;

            if (strpos($line, 'bin/sh -c') !== false)
                continue;

            if (strpos($line, 'auxww') !== false)
                continue;

            if (strpos($line, 'grep') !== false)
                continue;

            $process++;
        }

//        file_put_contents('isrunning', $process);

        return $process > 1;
    }
}