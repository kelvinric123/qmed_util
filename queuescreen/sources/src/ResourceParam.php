<?php

namespace Rasque;

class ResourceParam
{
	public function getTemp()
	{
		return (int) trim(shell_exec('cat /sys/class/thermal/thermal_zone0/temp')) / 1000;
		
		// return trim(str_replace(['temp=', "'C"], '', shell_exec('vcgencmd measure_temp')));
	}
	
	public function getMemory()
	{
		$memLines = explode("\n", shell_exec('free -m'));
		
		$mem = [];

		foreach ($memLines as $line) {
			if (strpos($line, 'Mem:') === false)
				continue;
				
			$parts = preg_split('/\s+/', $line);
			
			return [
				'total' => $parts[1],
				'used' => $parts[2],
				'free' => $parts[3]
			];
		}
	}
	
	// https://github.com/pear/Net_Wifi/blob/master/Net/Wifi.php
	public function getConnectionStatus()
	{
		$iwconfig = shell_exec('/sbin/iwconfig 2>&1; echo $?');
	
		$fields = [
			'bit_rate' => '/Bit Rate[:=]([0-9.]+) [mk]b\\/s/i',
			'link_quality' => '/Link Quality[:=](-?[0-9]+)/'
		];
		
		$status = [];
		
		foreach ($fields as $key => $regex) {
			if (preg_match($regex, $iwconfig, $arMatches)) {
				$status[$key] = $arMatches[1];
			}
		}
		
		if (isset($status['link_quality']))
			$status['link_quality'] = round(((int) $status['link_quality']) / 70, 2);
		
        return $status;
	}
	
	public function getStorage($partition = '/dev/root')
	{
		$memLines = explode("\n", shell_exec('df'));
		
		$mem = [];

		foreach ($memLines as $line) {
			if (strpos($line, $partition) === false)
				continue;
				
			$parts = preg_split('/\s+/', $line);
			
			return [
				'total' => $parts[1],
				'used' => $parts[2],
				'available' => $parts[3]
			];
		}
	}

	public function hasInternet()
	{
		return @file_get_contents('https://google.com') ? true : false;
	}

    public function getVersion()
    {
        return trim(shell_exec('git show --format="%h" --no-patch'));
	}
	
	public function toArray()
	{
		return [
		    'version' => $this->getVersion(),
			'temp' => $this->getTemp(),
			'memory' => $this->getMemory(),
			'storage' => $this->getStorage(),
			'connection_status' => $this->getConnectionStatus(),
			'has_internet' => $this->hasInternet()
		];
	}
}
