<?php

namespace tobimori\Mux;

use Kirby\Cms\Files;

trait HasMuxFiles
{
	use HasMuxApiClient;

	public function files(): Files
	{
		if ($this->files !== null) {
			return $this->files;
		}

		$files = new Files([], $this);


		foreach ($assets = $this->assetsApi()->listAssets()->getData() as $asset) {
			$files->add(new MuxVideo([
				'filename' => $asset->getId(),
				'template' => 'mux-video',
				'uuid' => $asset->getId(),
				'parent' => $this,
			]));
		}

		return $this->files = $files->add(parent::files());
	}
}
