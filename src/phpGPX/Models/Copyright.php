<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 22:20
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\SerializationHelper;

/**
 * Class Copyright
 * Information about the copyright holder and any license governing use of this file.
 * By linking to an appropriate license, you may place your data into the public domain or grant additional usage rights.
 * @package phpGPX\Models
 */
class Copyright implements Summarizable
{
	/**
	 * Copyright holder (TopoSoft, Inc.)
	 * Year of copyright.
	 * Link to external file containing license text.
	 */
	public function __construct(
		public string $author = '',
		public ?string $year = null,
		public ?string $license = null
	) {
	}

	/**
	 * Serialize object to array
	 */
	public function toArray(): array
	{
		return [
			'author' => $this->author,
			'year' => SerializationHelper::stringOrNull($this->year),
			'license' => SerializationHelper::stringOrNull($this->license)
		];
	}
}
