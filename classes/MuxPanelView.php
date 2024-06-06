<?php

namespace tobimori\Mux;

use Kirby\Panel\File as Panel;
use Kirby\Toolkit\I18n;

class MuxPanelView extends Panel
{
	/**
	 * Breadcrumb array
	 */
	public function breadcrumb(): array
	{
		$breadcrumb = parent::breadcrumb();
		array_pop($breadcrumb);

		// add the file
		$breadcrumb[] = [
			'label' => $this->model->title()->value(),
			'link' => $this->url(true),
		];

		return $breadcrumb;
	}

	/**
	 * Returns the data array for the
	 * view's component props
	 * @internal
	 */
	public function props(): array
	{
		$file = $this->model;
		$dimensions = $file->dimensions();

		return array_merge(
			parent::props(),
			[
				'model' => array_merge(
					parent::props()['model'],
					[
						'filename' => $file->title()->value(),
					]
				),
				'preview' => [
					'url' => $file->url(),
					'details' => [
						[
							'title' => I18n::translate('mime'),
							'text' => I18n::translate('mux.video')
						],
						[
							'title' => I18n::translate('mux.status'),
							'text' => I18n::translate('mux.status.' . $file->muxAsset()->getStatus(), $file->muxAsset()->getStatus())
						],
						[
							'title' => I18n::translate('mux.assetId'),
							'text' => $file->assetId()
						],
						[
							'title' => I18n::translate('mux.duration'),
							'text' => $file->niceDuration()
						],
						[
							'title' => I18n::translate('dimensions'),
							'text' => $file->dimensions() . ' ' . I18n::translate('pixel')
						],
						[
							'title' => I18n::translate('orientation'),
							'text' => I18n::translate('orientation.' . $dimensions->orientation())
						],
					]
				]
			]
		);
	}

	/**
	 * Returns the data array for
	 * this model's Panel view
	 * @internal
	 */
	public function view(): array
	{
		return array_merge(parent::view(), [
			'title' => $this->model->title()->value(),
		]);
	}
}
