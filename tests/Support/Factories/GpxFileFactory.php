<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\GpxFile;

/**
 * Factory for creating GpxFile instances for testing.
 */
class GpxFileFactory
{
	/**
	 * Create a GpxFile with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return GpxFile
	 */
	public static function create(array $overrides = []): GpxFile
	{
		$creator = $overrides['creator'] ?? 'phpGPX Test Suite';

		$gpxFile = new GpxFile($creator);

		if (isset($overrides['metadata'])) {
			$gpxFile->metadata = $overrides['metadata'];
		}

		if (isset($overrides['waypoints'])) {
			$gpxFile->waypoints = $overrides['waypoints'];
		}

		if (isset($overrides['routes'])) {
			$gpxFile->routes = $overrides['routes'];
		}

		if (isset($overrides['tracks'])) {
			$gpxFile->tracks = $overrides['tracks'];
		}

		return $gpxFile;
	}

	/**
	 * Create a GpxFile with metadata.
	 *
	 * @return GpxFile
	 */
	public static function createWithMetadata(): GpxFile
	{
		$metadata = MetadataFactory::create();

		return self::create(['metadata' => $metadata]);
	}

	/**
	 * Create a GpxFile with waypoints.
	 *
	 * @param int $count Number of waypoints to create
	 * @return GpxFile
	 */
	public static function createWithWaypoints(int $count = 3): GpxFile
	{
		$waypoints = PointFactory::createSequence($count);

		return self::create(['waypoints' => $waypoints]);
	}

	/**
	 * Create a GpxFile with tracks.
	 *
	 * @param int $count Number of tracks to create
	 * @return GpxFile
	 */
	public static function createWithTracks(int $count = 1): GpxFile
	{
		$tracks = [];
		for ($i = 0; $i < $count; $i++) {
			$tracks[] = TrackFactory::createWithPoints(5);
		}

		return self::create(['tracks' => $tracks]);
	}
}
