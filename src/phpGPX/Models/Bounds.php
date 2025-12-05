<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 22:03
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use InvalidArgumentException;
use phpGPX\Helpers\GpxValidator;

/**
 * Class Bounds
 * Two lat/lon pairs defining the extent of an element.
 * @see https://www.topografix.com/GPX/1/1/#type_boundsType
 * @package phpGPX\Models
 */
class Bounds implements Summarizable
{
	/**
	 * Create a new Bounds object with required coordinates.
	 *
	 * All four coordinates are required per GPX 1.1 schema.
	 * Validates coordinate ranges and logical consistency.
	 *
	 * @param float $minLatitude Minimum latitude (-90.0 to 90.0)
	 * @param float $minLongitude Minimum longitude (-180.0 to 180.0)
	 * @param float $maxLatitude Maximum latitude (-90.0 to 90.0)
	 * @param float $maxLongitude Maximum longitude (-180.0 to 180.0)
	 * @throws InvalidArgumentException If any coordinate is out of range or logical consistency fails
	 */
	public function __construct(
		public float $minLatitude,
		public float $minLongitude,
		public float $maxLatitude,
		public float $maxLongitude,
	) {
		// Validate latitude ranges
		GpxValidator::validateLatitude($minLatitude);
		GpxValidator::validateLatitude($maxLatitude);

		// Validate longitude ranges
		GpxValidator::validateLongitude($minLongitude);
		GpxValidator::validateLongitude($maxLongitude);

		// Validate logical consistency - minlat <= maxlat
		if ($minLatitude > $maxLatitude) {
			throw new InvalidArgumentException(
				sprintf(
					'Minimum latitude (%f) cannot be greater than maximum latitude (%f)',
					$minLatitude,
					$maxLatitude,
				),
			);
		}

		// Validate logical consistency - minlon <= maxlon
		if ($minLongitude > $maxLongitude) {
			throw new InvalidArgumentException(
				sprintf(
					'Minimum longitude (%f) cannot be greater than maximum longitude (%f)',
					$minLongitude,
					$maxLongitude,
				),
			);
		}
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
			'maxlon' => $this->maxLongitude,
		];
	}
}
