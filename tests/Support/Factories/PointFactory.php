<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Point;

/**
 * Factory for creating Point instances for testing.
 */
class PointFactory
{
	/**
	 * Generate a random valid latitude value.
	 * 
	 * @return float A latitude value between -90.0 and 90.0 (inclusive)
	 */
	public static function randomLatitude(): float
	{
		// Generate latitude between -90.0 and 90.0
		return mt_rand(-90000, 90000) / 1000.0;
	}

	/**
	 * Generate a random valid longitude value.
	 * 
	 * @return float A longitude value between -180.0 (inclusive) and 180.0 (exclusive)
	 */
	public static function randomLongitude(): float
	{
		// Generate longitude between -180.0 and 179.999
		return mt_rand(-180000, 179999) / 1000.0;
	}

	/**
	 * Create a Point with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Point
	 */
	public static function create(array $overrides = []): Point
	{
		$pointType = $overrides['pointType'] ?? Point::TRACKPOINT;
		$latitude = $overrides['latitude'] ?? 54.9328621088893;
		$longitude = $overrides['longitude'] ?? 9.860624216140083;
		
		$point = new Point($pointType, $latitude, $longitude);
		$point->elevation = $overrides['elevation'] ?? null;
		$point->time = $overrides['time'] ?? null;
		$point->name = $overrides['name'] ?? null;
		$point->description = $overrides['description'] ?? null;
		
		return $point;
	}

	/**
	 * Create a Point with elevation.
	 *
	 * @param float $elevation The elevation value
	 * @return Point
	 */
	public static function createWithElevation(float $elevation): Point
	{
		return self::create(['elevation' => $elevation]);
	}

	/**
	 * Create a Point at specific coordinates.
	 *
	 * @param float $latitude The latitude
	 * @param float $longitude The longitude
	 * @return Point
	 */
	public static function createAtCoordinates(float $latitude, float $longitude): Point
	{
		return self::create([
			'latitude' => $latitude,
			'longitude' => $longitude,
		]);
	}

	/**
	 * Create a sequence of Points along a path.
	 *
	 * @param int $count Number of points to create
	 * @param float $startLat Starting latitude
	 * @param float $startLon Starting longitude
	 * @param float $increment Increment for each point (default: 0.01)
	 * @return array Array of Point objects
	 */
	public static function createSequence(
		int $count,
		float $startLat = 54.0,
		float $startLon = 9.0,
		float $increment = 0.01
	): array {
		$points = [];
		
		for ($i = 0; $i < $count; $i++) {
			$points[] = self::create([
				'latitude' => $startLat + ($i * $increment),
				'longitude' => $startLon + ($i * $increment),
			]);
		}
		
		return $points;
	}
}
