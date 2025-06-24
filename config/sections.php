<?php

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

return [
	'files' => A::merge(
		// since we can't use "extends", we have to manually merge the sections/files.php config from Kirby core
		require_once App::instance()->root('kirby') . '/config/sections/files.php',
		[
			'props' => [
				/**
				 * Setup for the main text in the list or cards. By default this will display the filename.
				 */
				'text' => function ($text = '{{ file.title.or(file.filename) }}') {
					return I18n::translate($text, $text);
				}
			],
		]
	)
];
