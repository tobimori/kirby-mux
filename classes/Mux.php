<?php

namespace tobimori\Mux;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use MuxPhp\Models\Asset as MuxAsset;
use GuzzleHttp\Client;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Api\PlaybackIDApi;
use MuxPhp\Configuration;

class Mux
{
	/**
	 * Returns the API client configuration for Mux
	 */
	protected static function muxConfig(): Configuration
	{
		$tokenId = static::resolveOption('tokenId');
		$tokenSecret = static::resolveOption('tokenSecret');
		if ($tokenId === null || $tokenSecret === null) {
			throw new Exception('[Mux] Secrets not set');
		}

		return Configuration::getDefaultConfiguration()
			->setUsername($tokenId)
			->setPassword($tokenSecret);
	}

	/**
	 * Resolves an option from the plugin options
	 */
	public static function resolveOption(string $key): mixed
	{
		$value = App::instance()->option("tobimori.mux.$key");
		if (is_callable($value)) {
			$value = $value();
		}

		return $value;
	}

	/**
	 * Returns the assets API client
	 */
	public static function assetsApi(): AssetsApi
	{
		return new AssetsApi(new Client(), static::muxConfig());
	}

	/**
	 * Returns the playback ID API client
	 */
	public static function playbackIdApi(): PlaybackIDApi
	{
		return new PlaybackIDApi(new Client(), static::muxConfig());
	}

	/**
	 * Returns the Kirby cache instance
	 */
	public static function cache(): Cache
	{
		return App::instance()->cache('tobimori.mux');
	}

	/**
	 * Cached method to get all assets
	 */
	public static function assets(): array
	{
		if ($ids = static::cache()->get('index')) {
			return A::map(Str::split($ids, ','), function ($id) {
				$cachedObject = static::cache()->get($id);
				if ($cachedObject) {
					return unserialize($cachedObject);
				}

				// fetch the asset from the API, since it's not cached
				$asset = static::assetsApi()->getAsset($id)->getData();
				static::cache()->set($id, serialize($asset));

				return $asset;
			});
		}

		$assets = static::assetsApi()->listAssets(['limit' => 100000])->getData();
		foreach ($assets as $asset) {
			// cache each asset individually
			static::cache()->set($asset->getId(), serialize($asset));
		}

		// cache the index
		static::cache()->set('index', A::join(A::pluck($assets, 'id'), ','));

		return $assets;
	}

	public static function addAssetCache(string $id, MuxAsset $asset = null): bool
	{
		if (!$asset) {
			$asset = static::assetsApi()->getAsset($id)->getData();
		}

		static::cache()->set($id, serialize($asset));

		$index = static::cache()->get('index');
		if ($index) {
			$ids = Str::split($index, ',');
			if (A::has($ids, $id)) {
				return true;
			}

			$ids[] = $id;
			$index = A::join($ids, ',');
		} else {
			$index = $id;
		}

		return static::cache()->set('index', $index);
	}

	/**
	 * Remove asset cache and index
	 */
	public static function removeAssetCache(string $id): bool
	{
		static::cache()->remove($id); // remove the asset from the cache

		// remove the asset from the index
		$index = static::cache()->get('index');
		if (!$index) {
			return false;
		}

		$ids = Str::split($index, ',');
		$index = A::filter($ids, function ($assetId) use ($id) {
			return $assetId !== $id;
		});

		return static::cache()->set('index', A::join($index, ','));
	}

	/**
	 * Get a single asset by ID
	 */
	public static function asset(string $id): MuxAsset|null
	{
		$data = unserialize(
			static::cache()->getOrSet($id, fn () => serialize(static::assetsApi()->getAsset($id)->getData()))
		);

		if ($data !== null) {
			static::addAssetCache($id, $data);
		}

		return $data;
	}

	/**
	 * Parses a passthrough string into an array
	 */
	public static function parsePassthroughString(string|null $string): array
	{
		if (!$string) {
			return [];
		}

		return A::reduce(Str::split($string, ';'), function ($prev, $pair) {
			[$key, $value] = Str::split($pair, '=');
			$prev[$key] = $value;
			return $prev;
		}, []);
	}

	public static function createPassthroughString(array $data): string
	{
		return A::join(A::map(array_keys($data), fn ($key) => "{$key}={$data[$key]}"), ';');
	}
}
