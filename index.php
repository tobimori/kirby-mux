<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;

App::plugin('tobimori/mux', [
	'options' => [
		'tokenId' => '40795c59-2aed-4f2b-903c-18a61dc6e714',
		'tokenSecret' => '8ZVFpG45S25syzBP9uhSfQ79oqx+O3jW1CztcQP2bQqECiCnpu1adhSYu3Uk6FROJP33cbhIRep',
	],
	'blueprints' => [
		'files/mux-video' => __DIR__ . '/blueprints/files/mux-video.yml',
	]
]);
