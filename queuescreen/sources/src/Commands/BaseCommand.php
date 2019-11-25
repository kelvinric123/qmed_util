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

    /**
     * @var string
     */
    protected $basePath;

    public function __construct($basePath)
    {
        $basePath = trim($basePath, '/');

        $this->basePath = $basePath;

        $this->configPath = $basePath . '/config.json';

        if (file_exists($this->configPath))
            $this->config = json_decode(file_get_contents($this->configPath), true);
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