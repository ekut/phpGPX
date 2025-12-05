<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 22:03
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

class Bounds implements Summarizable
{
	/**
	 * Minimal latitude in file.
	 */
	public function __construct(
		public ?float $minLatitude,
		public ?float $minLongitude,
		public ?float $maxLatitude,
		public ?float $maxLongitude
	) {
	}

	/**
	 * Serialize object to array
	 */
	public function toArray(): array
	{
		return [
			'minlat' => $this->minLatitude,
			'minlon' => $this->minLongitude,
			'maxlat' => $this->maxLatitude,
			'maxlon' => $this->maxLongitude
		];
	}
}
