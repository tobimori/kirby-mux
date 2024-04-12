<?php

namespace tobimori\Mux;

use GuzzleHttp\Client;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Configuration;

trait HasMuxApiClient
{
	protected function muxConfig(): Configuration
	{
		$tokenId = App::instance()->option('tobimori.mux.tokenId');
		if (is_callable($tokenId)) {
			$tokenId = $tokenId();
		}

		$tokenSecret = App::instance()->option('tobimori.mux.tokenSecret');
		if (is_callable($tokenSecret)) {
			$tokenSecret = $tokenSecret();
		}

		if ($tokenId === null || $tokenSecret === null) {
			throw new Exception('[Mux] Secrets not set');
		}

		return Configuration::getDefaultConfiguration()
			->setUsername($tokenId)
			->setPassword($tokenSecret);
	}

	protected function assetsApi(): AssetsApi
	{
		return new AssetsApi(new Client(), $this->muxConfig());
	}
}
