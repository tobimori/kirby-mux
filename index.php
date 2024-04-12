<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;

App::plugin('tobimori/kirby-mux', [
	'options' => [
		'tokenId' => env('MUX_TOKEN_ID'),
		'tokenSecret' => env('MUX_TOKEN_SECRET'),
	],
	'blueprints' => [
		'files/mux-video' => __DIR__ . '/blueprints/files/mux-video.yml',
	]
]);
