<?php

namespace Rasque;

class ResourceParam
{
	public function getTemp()
	{
		return trim(str_replace(['temp=', "'C"], '', shell_exec('vcgencmd measure_temp')));
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
	
	public function toArray()
	{
		return [
			'temp' => $this->getTemp(),
			'memory' => $this->getMemory(),
		];
	}
}
