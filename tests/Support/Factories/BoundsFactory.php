<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Bounds;

/**
 * Factory for creating Bounds instances for testing.
 */
class BoundsFactory
{
	/**
	 * Create a Bounds with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Bounds
	 */
	public static function create(array $overrides = []): Bounds
	{
		$minLatitude = $overrides['minLatitude'] ?? 54.0;
		$minLongitude = $overrides['minLongitude'] ?? 9.0;
		$maxLatitude = $overrides['maxLatitude'] ?? 55.0;
		$maxLongitude = $overrides['maxLongitude'] ?? 10.0;

		return new Bounds($minLatitude, $minLongitude, $maxLatitude, $maxLongitude);
	}

	/**
	 * Create a Bounds with random valid coordinates.
	 *
	 * @return Bounds
	 */
	public static function createRandom(): Bounds
	{
		$lat1 = PointFactory::randomLatitude();
		$lat2 = PointFactory::randomLatitude();
		$lon1 = PointFactory::randomLongitude();
		$lon2 = PointFactory::randomLongitude();

		// Ensure min <= max
		$minLatitude = min($lat1, $lat2);
		$maxLatitude = max($lat1, $lat2);
		$minLongitude = min($lon1, $lon2);
		$maxLongitude = max($lon1, $lon2);

		return new Bounds($minLatitude, $minLongitude, $maxLatitude, $maxLongitude);
	}
}
