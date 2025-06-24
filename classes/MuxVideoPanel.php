<?php

namespace tobimori\Mux;

use Kirby\Panel\File as FilePanel;

class MuxVideoPanel extends FilePanel
{
	/**
	 * Returns the model props
	 */
	public function props(): array
	{
		$props = parent::props();
		
		// Override the filename with the title for display
		if ($this->model instanceof MuxVideo) {
			$props['filename'] = $this->model->title()->value();
		}
		
		return $props;
	}
	
	/**
	 * Breadcrumb array
	 */
	public function breadcrumb(): array
	{
		$breadcrumb = parent::breadcrumb();
		
		// Update the last breadcrumb item to use the title
		if (!empty($breadcrumb) && $this->model instanceof MuxVideo) {
			$lastIndex = count($breadcrumb) - 1;
			$breadcrumb[$lastIndex]['label'] = $this->model->title()->value();
		}
		
		return $breadcrumb;
	}
}