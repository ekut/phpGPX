<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Segment;

/**
 * Factory for creating Segment instances for testing.
 */
class SegmentFactory
{
	/**
	 * Create a Segment with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Segment
	 */
	public static function create(array $overrides = []): Segment
	{
		$segment = new Segment();
		$segment->points = $overrides['points'] ?? [];
		
		return $segment;
	}

	/**
	 * Create a Segment with a specified number of points.
	 *
	 * @param int $pointCount Number of points to create
	 * @return Segment
	 */
	public static function createWithPoints(int $pointCount): Segment
	{
		$points = PointFactory::createSequence($pointCount);
		return self::create(['points' => $points]);
	}
}
