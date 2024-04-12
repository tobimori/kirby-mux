<?php

namespace tobimori\Mux;

use Kirby\Cms\File;
use MuxPhp\Models\Asset;
use MuxPhp\Models\PlaybackID;

class MuxVideo extends File
{
	use HasMuxApiClient;

	public function muxAsset(): Asset
	{
		return $this->assetsApi()->getAsset($this->assetId())->getData();
	}

	public function assetId(): string
	{
		return $this->filename();
	}

	public function playbackId(): PlaybackID
	{
		return $this->muxAsset()->getPlaybackIds()[0];
	}

	public function url(): string
	{
		return 'https://stream.mux.com/' . $this->playbackId()->getId();
	}

	public function thumb($options = null): MuxVideo
	{
		return $this;
	}

	/**
	 * Deletes the asset from Mux
	 */
	public function delete(): bool
	{
		return $this->commit('delete', ['file' => $this], function ($file) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			foreach ($file->storage()->all() as $version => $lang) {
				$file->storage()->delete($version, $lang);
			}

			$this->assetsApi()->deleteAsset($file->assetId());

			// remove the file from the sibling collection
			$file->parent()->files()->remove($file);

			return true;
		});
	}

	/**
	 * Returns the permissions object for this file
	 */
	public function permissions(): MuxPermissions
	{
		return new MuxPermissions($this);
	}
}
