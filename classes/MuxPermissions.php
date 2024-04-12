<?php

namespace tobimori\Mux;

use Kirby\Cms\FilePermissions;

class MuxPermissions extends FilePermissions
{
	/**
	 * filename = mux asset id
	 */
	protected function canChangeName(): bool
	{
		return false;
	}
}
