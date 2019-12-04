<?php

namespace Rasque;

class DeviceInfo
{
	public function __construct($basePath)
	{
		$this->basePath = rtrim($basePath, '/');
	}
	
	public static create()
	{
		return new static(realpath(__DIR__ . '/../..'));
	}
	
	public function getDeviceId()
	{
		$path = $this-basePath . '/bin/device-id.sh';
		
		return shell_exec('sh ' . $path);
	}
}
