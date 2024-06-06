<?php

namespace tobimori\Mux;

use Kirby\Cms\File;
use Kirby\Cms\Files;

/**
 * Add support for Mux assets to any Kirby CMS page
 */
trait HasMuxFiles
{
	public function files(): Files
	{
		if ($this->files !== null) {
			return $this->files;
		}

		// filter out mux-video files
		// they will be added directly from the API
		$files = parent::files();

		$uuid = $this->uuid()->id();
		foreach (Mux::assets() as $asset) {
			$data = Mux::parsePassthroughString($asset->getPassthrough());

			// check for matching uuid if set, or if global scope in plugin is used
			if (isset($data['parent']) ? $uuid === $data['parent'] : Mux::resolveOption('globalScope') === true) {
				$files->add(MuxVideo::fromApiResource($asset, $this));
			}
		}

		$this->files = $files;

		// if the kirby server got out of sync with the mux server, delete obsolete files
		// (not recognized via the mux API, but still present in the kirby filesystem)
		$this->clearObsoleteFiles();

		return $this->files;
	}

	/**
	 * Clear obsolete files
	 */
	protected function clearObsoleteFiles(): Files
	{
		$files = $this->files;

		foreach ($files->filter(function ($file) {
			return !($file instanceof MuxVideo) && $file->template() === 'mux-video';
		}) as $file) {
			if (!Mux::asset($file->filename())) {
				$this->kirby()->impersonate('kirby', fn () => $file->delete());
			}
		}

		return $files;
	}

	/**
	 * Creates a new file
	 *
	 * @param bool $move If set to `true`, the source will be deleted
	 */
	public function createFile(array $props, bool $move = false): File|MuxVideo
	{
		if ($props['template'] !== 'mux-video') {
			return parent::createFile($props, $move);
		}

		$props = array_merge($props, [
			'parent' => $this,
			'url'    => null
		]);

		return MuxVideo::create($props, $move);
	}
}
