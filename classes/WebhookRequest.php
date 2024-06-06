<?php

namespace tobimori\Mux;

use Kirby\Cms\App;
use Kirby\Http\Request;
use Kirby\Toolkit\Str;

class WebhookRequest extends Request
{
	/**
	 * Creates a new WebhookRequest object
	 * from a Kirby Request object
	 */
	public static function current(): self
	{
		return new WebhookRequest(array_merge(App::instance()->request()->__debugInfo(), App::instance()->request()->options));
	}

	/**
	 * Verifies the signature of a webhook request
	 */
	public function verifySignature(): bool
	{
		$secret = Mux::resolveOption('webhookSecret');
		if ($secret === null) {
			return true;
		}

		// format is 't=[timestamp],v1=[hash]'
		$signature = $this->header('Mux-Signature');
		$split = explode(',', $signature);

		$timestamp = Str::after($split[0], 't=');
		$payload = $timestamp . "." . $this->body()->toJson();
		$signature = hash_hmac('sha256', $payload, $secret);

		return hash_equals($signature, Str::after($split[1], 'v1='));
	}

	/**
	 * Handles and executes the webhook request
	 */
	public function handle(): void
	{
		$body = $this->body()->toArray();

		// available types
		// https://docs.mux.com/core/listen-for-webhooks#types-of-events
		(match ($body['type']) {
			'video.asset.ready' => fn () => $this->handleAssetReady(),
			'video.asset.deleted' => fn () => $this->handleAssetDeleted(),
			'video.asset.updated' => fn () => Mux::cache()->flush(),
			default => fn () => null,
		})();
	}

	/**
	 * Handles the 'video.asset.ready' webhook event
	 * Deletes original video files, creates dummy content files
	 */
	protected function handleAssetReady(): void
	{
		$kirby = App::instance();
		$data = $this->body()->toArray()['data'];

		$passthrough = Mux::parsePassthroughString($data['passthrough']);

		// delete original file
		$originalFile = $kirby->site()->file("file://{$passthrough['uuid']}");
		$kirby->impersonate('kirby', fn () => $originalFile->delete());

		// clear cache for asset id
		Mux::removeAssetCache($data['id']);
	}

	/**
	 * Handles the 'video.asset.deleted' webhook event
	 * deletes dummy content files on disk
	 */
	protected function handleAssetDeleted(): void
	{
		$kirby = App::instance();

		// get asset id
		$id = $this->body()->toArray()['data']['id'];

		// delete file
		$file = $kirby->site()->file("file://{$id}");
		if ($file) { // file might be unknown if we deleted the asset from within kirby
			$kirby->impersonate('kirby', fn () => $file->delete());

			// clear cache for asset id
			Mux::removeAssetCache($id);
		}
	}
}
