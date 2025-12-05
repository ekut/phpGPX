<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Segment;
use phpGPX\Models\Track;

/**
 * Factory for creating Track instances for testing.
 */
class TrackFactory
{
	/**
	 * Create a Track with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Track
	 */
	public static function create(array $overrides = []): Track
	{
		$track = new Track();
		$track->name = $overrides['name'] ?? 'Test Track';
		$track->description = $overrides['description'] ?? null;
		$track->segments = $overrides['segments'] ?? [];

		return $track;
	}

	/**
	 * Create a Track with a specified number of points in a single segment.
	 *
	 * @param int $pointCount Number of points to create
	 * @return Track
	 */
	public static function createWithPoints(int $pointCount): Track
	{
		$track = self::create();
		$segment = SegmentFactory::createWithPoints($pointCount);
		$track->segments[] = $segment;

		return $track;
	}

	/**
	 * Create a Track with a specified number of segments.
	 *
	 * @param int $segmentCount Number of segments to create
	 * @param int $pointsPerSegment Number of points per segment (default: 5)
	 * @return Track
	 */
	public static function createWithSegments(int $segmentCount, int $pointsPerSegment = 5): Track
	{
		$track = self::create();

		for ($i = 0; $i < $segmentCount; $i++) {
			$track->segments[] = SegmentFactory::createWithPoints($pointsPerSegment);
		}

		return $track;
	}
}
