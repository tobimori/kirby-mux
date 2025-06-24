<?php

namespace tobimori\Mux;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;
use Kirby\Toolkit\I18n;

class MuxVideoPreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-mux-video-preview'
	) {}

	public static function accepts(File $file): bool
	{
		return $file instanceof MuxVideo;
	}

	public function details(): array
	{
		$details = [];

		if ($this->file instanceof MuxVideo) {
			$dimensions = $this->file->dimensions();

			$details = [
				[
					'title' => I18n::translate('mime'),
					'text' => I18n::translate('video')
				],
				[
					'title' => I18n::translate('mux.status'),
					'text' => I18n::translate('mux.status.' . $this->file->muxAsset()->getStatus())
				],
				[
					'title' => I18n::translate('mux.assetId'),
					'text' => $this->file->assetId()
				],
				[
					'title' => I18n::translate('mux.duration'),
					'text' => $this->file->niceDuration()
				],
				[
					'title' => I18n::translate('dimensions'),
					'text' => $dimensions . ' ' . I18n::translate('pixel')
				],
				[
					'title' => I18n::translate('orientation'),
					'text' => I18n::translate('orientation.' . $dimensions->orientation())
				]
			];
		}

		return $details;
	}

	public function props(): array
	{
		$props = parent::props();

		// add mux-specific properties
		if ($this->file instanceof MuxVideo) {
			$props['url'] = $this->file->url();
			$props['assetId'] = $this->file->assetId();
			$props['status'] = $this->file->muxAsset()->getStatus();
			$props['duration'] = $this->file->duration();
			$props['niceDuration'] = $this->file->niceDuration();

			if ($playbackId = $this->file->playbackId()) {
				$props['playbackId'] = $playbackId->getId();
			}
		}

		return $props;
	}
}
