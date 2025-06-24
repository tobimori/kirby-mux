<?php

namespace tobimori\Mux;

use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\Field;
use Kirby\Exception\Exception;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Image\Dimensions;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use MuxPhp\Models\Asset as MuxAsset;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackID;
use MuxPhp\Models\PlaybackPolicy;

class MuxVideo extends File
{
	protected MuxAsset|null $muxAsset = null;
	public function muxAsset(): MuxAsset
	{
		if ($this->muxAsset !== null) {
			return $this->muxAsset;
		}

		return $this->muxAsset = Mux::asset($this->assetId());
	}

	/**
	 * Turns a Mux API asset model into a Kirby file
	 */
	public static function fromApiResource(MuxAsset $asset, ModelWithContent $parent, string $title = null): static
	{
		return new MuxVideo([
			'filename' => $asset->getId(),
			'template' => 'mux-video',
			'parent' => $parent,
			'asset' => $asset,
			'title' => $title,
		]);
	}

	/**
	 * Creates a new File object
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		// Create content file if it doesn't exist
		// (this happens when a file is not uploaded via the panel)
		if (!F::exists($this->root())) {
			F::write($this->root(), '');
			$this->save([
				'title' => $props['title'] ?? $this->filename(),
				'uuid' => $this->filename(),
			]);
		}

		// store mux asset object if we got it from a list request already
		if ($props['asset'] !== null) {
			$this->muxAsset = $props['asset'];
		}
	}

	/**
	 * Creates a new file on disk and uploads it to Mux
	 */
	public static function create(array $props, bool $move = false): static
	{
		$props['content'] ??= [];
		$props['content']['title'] = $props['filename'];
		$props['template'] = 'mux-video-processing';
		$uploadedFile = File::create($props, $move);

		// create a new Mux asset
		$asset = Mux::assetsApi()->createAsset(
			new CreateAssetRequest([
				'input' => new InputSettings([
					'url' => $uploadedFile->url()
				]),
				'encoding_tier' => Mux::resolveOption('encodingTier', 'smart'),
				'playback_policy' => PlaybackPolicy::_PUBLIC,
				'passthrough' => Mux::createPassthroughString([
					'uuid' => $uploadedFile->uuid()->id(),
					'parent' => $uploadedFile->parent()->uuid()->id(),
				])
			])
		)->getData();

		Mux::addAssetCache($asset->getId(), $asset);
		return static::fromApiResource($asset, $props['parent'], $props['filename']);
	}

	/**
	 * Returns the title field or the filename as fallback
	 */
	public function title(): Field
	{
		return $this->content()->get('title')->or($this->filename());
	}

	/**
	 * Returns the asset ID for Mux
	 */
	public function assetId(): string
	{
		return $this->filename();
	}

	/**
	 * Returns the first playback ID
	 * expected to be the only one that is public
	 */
	public function playbackId(): PlaybackID|null
	{
		$ids = $this->muxAsset()->getPlaybackIds();
		return $ids !== null ? A::first($ids) : null;
	}

	/**
	 * Returns the Stream URL to the video
	 */
	public function url(): string
	{
		if ($this->playbackId() === null || $this->muxAsset()->getStatus() !== 'ready') {
			return '';
		}

		return "https://stream.mux.com/{$this->playbackId()->getId()}.m3u8";
	}

	/**
	 * Downloads & returns the thumbnail of the video
	 */
	public function thumb($options = null): Asset|MuxVideo
	{
		if ($this->playbackId() === null || $this->muxAsset()->getStatus() !== 'ready') {
			throw new Exception('[Mux] Video is not ready yet');
		}

		// kirby expects a *local* file/asset object to be returned
		// so it can show a custom thumbnail in the panel
		// we need to download the thumbnail from Mux and save it locally
		$path = $this->kirby()->root('media') . '/mux/' . $this->playbackId()->getId() . '.png';
		if (!F::exists($path)) {
			$request = Remote::get("https://image.mux.com/{$this->playbackId()->getId()}/thumbnail.png?time=0");
			F::write($path, $request->content());
		}

		$relativePath = Str::after($path, $this->kirby()->root('index'));
		return new Asset(Str::trim($relativePath, '/'));
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

			// delete the asset from Mux
			Mux::assetsApi()->deleteAsset($file->assetId());

			// delete dummy file
			F::remove($file->root());

			// remove the file from the sibling collection
			$file->parent()->files()->remove($file);

			// flush the cahce
			Mux::cache()->flush();

			return true;
		});
	}

	/**
	 * Returns the panel info object
	 */
	public function panel(): MuxPanelView
	{
		return new MuxPanelView($this);
	}

	/**
	 * Returns the Dimensions of the largest video track
	 */
	public function dimensions(): Dimensions
	{
		$tracks = $this->muxAsset()->getTracks(); // get all tracks
		if (!$tracks) return new Dimensions(0, 0); // return 0 if no tracks are found

		$tracks = A::filter($tracks, fn ($track) => $track->getType() === 'video'); // find the largest video track
		$track = A::first(A::sort($tracks, 'getMaxWidth', SORT_DESC)); // sort by width

		return new Dimensions($track->getMaxWidth(), $track->getMaxHeight());
	}

	/**
	 * Returns the aspect ratio of the video
	 */
	public function ratio(): float
	{
		return $this->dimensions()->ratio();
	}

	/**
	 * Change name is not supported for Mux videos, use changeTitle instead
	 * Function does nothing
	 */
	public function changeName(string $name, bool $sanitize = true, ?string $extension = null): static
	{
		return $this;
	}

	/**
	 * Change the page title
	 */
	public function changeTitle(
		string $title,
		string|null $languageCode = null
	): static {
		// if the `$languageCode` argument is not set and is not the default language
		// the `$languageCode` argument is sent as the current language
		if (
			$languageCode === null &&
			$language = $this->kirby()->language()
		) {
			if ($language->isDefault() === false) {
				$languageCode = $language->code();
			}
		}

		$this->update(['title' => $title], $languageCode);

		return $this;
	}

	/**
	 * Returns the duration of the video in seconds
	 */
	public function duration(): float
	{
		return $this->muxAsset()->getDuration() ?? 0;
	}

	/**
	 * Returns the duration of the video in a human readable format
	 */
	public function niceDuration(): string
	{
		$duration = $this->duration();
		$minutes = floor($duration / 60);
		$seconds = floor($duration) % 60;
		return sprintf('%02d:%02d', $minutes, $seconds);
	}
}
