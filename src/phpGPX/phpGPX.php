<?php

declare(strict_types=1);
/**
 * Created            26/08/16 13:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX;

use phpGPX\Enums\FileFormat;
use phpGPX\Models\GpxFile;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Parsers\RouteParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\Parsers\WaypointParser;
use RuntimeException;

/**
 * Class phpGPX
 * @package phpGPX
 */
class phpGPX
{
	/**
	 * @deprecated Use FileFormat::JSON instead
	 */
	public const string JSON_FORMAT = 'json';

	/**
	 * @deprecated Use FileFormat::XML instead
	 */
	public const string XML_FORMAT = 'xml';

	public const string PACKAGE_NAME = 'phpGPX';
	public const string VERSION = '1.3.0';

	/**
	 * Create Stats object for each track, segment and route
	 */
	public static bool $CALCULATE_STATS = true;

	/**
	 * Additional sort based on timestamp in Routes & Tracks on XML read.
	 * Disabled by default, data should be already sorted.
	 */
	public static bool $SORT_BY_TIMESTAMP = false;

	/**
	 * Default DateTime output format in JSON serialization.
	 */
	public static string $DATETIME_FORMAT = 'c';

	/**
	 * Default timezone for display.
	 * Data are always stored in UTC timezone.
	 */
	public static string $DATETIME_TIMEZONE_OUTPUT = 'UTC';

	/**
	 * Pretty print.
	 */
	public static bool $PRETTY_PRINT = true;

	/**
	 * In stats elevation calculation: ignore points with an elevation of 0
	 * This can happen with some GPS software adding a point with 0 elevation
	 */
	public static bool $IGNORE_ELEVATION_0 = true;

	/**
	 * Apply elevation gain/loss smoothing? If true, the threshold in
	 * ELEVATION_SMOOTHING_THRESHOLD and ELEVATION_SMOOTHING_SPIKES_THRESHOLD (if not null) applies
	 */
	public static bool $APPLY_ELEVATION_SMOOTHING = false;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the minimum elevation difference between considered points in meters
	 */
	public static int $ELEVATION_SMOOTHING_THRESHOLD = 2;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the maximum elevation difference between considered points in meters
	 */
	public static ?int $ELEVATION_SMOOTHING_SPIKES_THRESHOLD = null;

	/**
	 * Apply distance calculation smoothing? If true, the threshold in
	 * DISTANCE_SMOOTHING_THRESHOLD applies
	 */
	public static bool $APPLY_DISTANCE_SMOOTHING = false;

	/**
	 * if APPLY_DISTANCE_SMOOTHING is true
	 * the minimum distance between considered points in meters
	 */
	public static int $DISTANCE_SMOOTHING_THRESHOLD = 2;

	/**
	 * Load GPX file.
	 */
	public static function load(string $path): GpxFile
	{
		$xml = file_get_contents($path);

		if ($xml === false) {
			throw new RuntimeException("Failed to read file: {$path}");
		}

		return self::parse($xml);
	}

	/**
	 * Parse GPX data string.
	 */
	public static function parse(string $xml): GpxFile
	{
		$xml = simplexml_load_string($xml);

		// Parse creator (required by GPX 1.1 schema)
		$creator = isset($xml['creator']) ? (string)$xml['creator'] : self::getSignature();

		$gpx = new GpxFile($creator);

		// Parse metadata
		$gpx->metadata = isset($xml->metadata) ? MetadataParser::parse($xml->metadata) : null;

		// Parse waypoints
		$gpx->waypoints = isset($xml->wpt) ? WaypointParser::parse($xml->wpt) : [];

		// Parse tracks
		$gpx->tracks = isset($xml->trk) ? TrackParser::parse($xml->trk) : [];

		// Parse routes
		$gpx->routes = isset($xml->rte) ? RouteParser::parse($xml->rte) : [];

		return $gpx;
	}

	/**
  * Create library signature from name and version.
  */
	public static function getSignature(): string
	{
		return sprintf("%s/%s", self::PACKAGE_NAME, self::VERSION);
	}
}
