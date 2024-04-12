<?php

namespace tobimori\Mux;

use Kirby\Cms\File;

class MuxVideo extends File
{
	public function url(): string
	{
		return $this->url;
	}

	public function thumb($options = null): MuxVideo
	{
		return $this;
	}
}
