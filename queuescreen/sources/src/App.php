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

    public function reboot($reason)
    {
        $lastRebootTime = @file_get_contents($this->getBasePath() . '/last-reboot-time');

        if (!$lastRebootTime) {
            \Rasque\Logger::create()->log($reason);
            file_put_contents('last-reboot-time', time());
            shell_exec('sudo reboot -f');
            return;
        }

        // ONLY reboot when there's no reboot under the last 5 minutes
        if (time() > strtotime('+5 minutes', $lastRebootTime)) {
            \Rasque\Logger::create()->log($reason);
            file_put_contents('last-reboot-time', time());
            shell_exec('sudo reboot -f');
            return;
        }

        return;
    }
}