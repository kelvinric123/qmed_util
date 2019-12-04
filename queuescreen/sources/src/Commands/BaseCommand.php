<?php

namespace Rasque\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rasque\DeviceInfo;
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

    protected $deviceId;

    /** @var Client $http */
    protected $http;

    /** @var string|null $installationId  */
    protected $installationId;

    public function __construct($basePath)
    {
        $basePath = rtrim($basePath, '/');

        $this->basePath = realpath($basePath);

        $this->configPath = $basePath . '/config.json';

        if (file_exists($this->configPath))
            $this->config = json_decode(file_get_contents($this->configPath), true);
        else
            $this->config = [];

        $this->deviceId = DeviceInfo::create()->getDeviceId();

        $baseUrl = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        $this->http = new Client([
            'base_uri' => $baseUrl
        ]);

        // check if this raspberry already linked
        try {
            $lookup = json_decode($this->http->request('GET', '/apis/installations/screen-lookup?device_id=' . $this->deviceId)->getBody(), true)['data'];

            $this->installationId = $lookup['installation_id'];
        } catch (ClientException $e) {
            $this->installationId = null;
        }

        parent::__construct();
    }

    public function getConfig($name, $default = null)
    {
        if (!isset($this->config[$name]))
            return $default;

        return $this->config[$name];
    }

    public function write(OutputInterface $output, $message)
    {
        $output->write($message);

        return 0;
    }

    public function getInstallationApiPath()
    {
        $host = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        if (!isset($this->config['installation_id']))
            throw new \Exception('Installation is required.');

        return $host . '/apis/installations/' . $this->config['installation_id'];
    }

    public function getScreenApiPath()
    {
        $host = isset($this->config['host']) ? $this->config['host'] : 'https://qmed.asia';

        return $host . '/apis/installations/screens/' . DeviceInfo::create()->getDeviceId();
    }
}
