<?php

namespace Rasque;

class DeviceInfo
{
    /** @var string $basePath */
    protected $basePath;

    public function __construct($basePath)
	{
		$this->basePath = rtrim($basePath, '/');
	}
	
	public static function create()
	{
		return new static(realpath(__DIR__ . '/../..'));
	}
	
	public function getDeviceId()
	{
	    // for win based development test purpose
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		    return '1234567';

        $path = $this->basePath . '/bin/device-id.sh';

        return shell_exec('sh ' . $path);
	}
}
