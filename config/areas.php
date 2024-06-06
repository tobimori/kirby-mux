<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\I18n;
use tobimori\Mux\MuxVideo;

return [
	'site' => function (App $kirby) {
		$files = require_once $kirby->root('kirby') . '/config/areas/files/dialogs.php';

		return [
			'dialogs' => [
				'page.file.changeName' => [
					'load' => function (string $path, string $filename) use ($files) {
						$file = Find::file($path, $filename);

						// handle title instead of filename for mux videos
						if ($file instanceof MuxVideo) {
							return [
								'component' => 'k-form-dialog',
								'props' => [
									'fields' => [
										'title' => [
											'label' => I18n::translate('title'),
											'type' => 'text',
											'required' => true,
											'icon' => 'title',
											'preselect' => true
										]
									],
									'submitButton' => I18n::translate('rename'),
									'value' => [
										'title' => $file->title()->value(),
									]
								]
							];
						}

						// use default component for non-mux files
						return $files['changeName']['load']($path, $filename);
					},
					'submit' => function (string $path, string $filename) use ($files) {
						$file = Find::file($path, $filename);

						// handle title instead of filename for mux videos
						if ($file instanceof MuxVideo) {
							$request = App::instance()->request();
							$title = trim($request->get('title', ''));

							if ($file->title()->value() !== $title) {
								$file->changeTitle($title);
							}

							return true;
						}

						// use default component for non-mux files
						return $files['changeName']['submit']($path, $filename);
					}
				]
			]
		];
	},
];
