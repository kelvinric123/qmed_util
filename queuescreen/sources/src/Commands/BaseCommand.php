<?php

namespace Rasque\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /**
     * @var null|string
     */
    protected $configPath;

    /**
     * @var bool|string
     */
    protected $config;

    public function __construct($configPath)
    {
        $this->configPath = $configPath;

        if (file_exists($configPath))
            $this->config = json_decode(file_get_contents($configPath), true);
        else
            $this->config = [];

        parent::__construct();
    }

    public function write(OutputInterface $output, $message)
    {
        $output->write($message);

        return 0;
    }
}