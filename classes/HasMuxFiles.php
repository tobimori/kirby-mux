<?php

namespace tobimori\Mux;

use Kirby\Cms\Files;
use MuxPhp\Configuration;

trait HasMuxFiles
{
	private function muxConfig()
	{
		return Configuration::getDefaultConfiguration()
			->setUsername('YOUR_USERNAME')
			->setPassword('YOUR_PASSWORD');
	}

	public function files(): Files
	{
		if ($this->files !== null) {
			return $this->files;
		}

		$files = new Files([], $this);




		return $this->files = $files;
	}
}
