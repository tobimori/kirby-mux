<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;

App::plugin(
	name: 'tobimori/mux', 
extends: [
	'options' => [
		'cache' => true,
		'tokenId' => null,
		'tokenSecret' => null,
		'signingSecret' => null,
		'globalScope' => false,
		'encodingTier' => 'smart' // or 'baseline'
	],
	'areas' => require __DIR__ . '/config/areas.php',
	'routes' => require __DIR__ . '/config/routes.php',
	'sections' => require __DIR__ . '/config/sections.php',
	'blueprints' => [
		'files/mux-video' => __DIR__ . '/blueprints/files/mux-video.yml',
		'files/mux-video-processing' => __DIR__ . '/blueprints/files/mux-video-processing.yml',
	],
	// get all files from /translations and register them as language files
	'translations' => A::keyBy(
		A::map(
			Dir::read(__DIR__ . '/translations'),
			function ($file) {
				$translations = [];
				foreach (Json::read(__DIR__ . '/translations/' . $file) as $key => $value) {
					$translations["mux.{$key}"] = $value;
				}

				return A::merge(
					['lang' => F::name($file)],
					$translations
				);
			}
		),
		'lang'
	),
],

version: '1.0.0');

