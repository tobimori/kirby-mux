<?php

use Kirby\Http\Response;
use tobimori\Mux\WebhookRequest;

return [
	[
		'pattern' => 'mux-endpoint',
		'method' => 'POST',
		'action' => function () {
			$request = WebhookRequest::current();

			// verify signature
			if (!$request->verifySignature()) {
				return new Response('Invalid signature', 403); // forbidden
			}

			// handle webhook
			try {
				$request->handle();
				return new Response('ok', 200);
			} catch (Exception $e) {
				return new Response('Error handling webhook', 500);
			}
		}
	]
];
