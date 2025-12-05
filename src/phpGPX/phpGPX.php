<?php

declare(strict_types=1);
/**
 * Created            26/08/16 13:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX;

use phpGPX\Models\GpxFile;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Parsers\RouteParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\Parsers\WaypointParser;

/**
 * Class phpGPX
 * @package phpGPX
 */
class phpGPX
{
	public const JSON_FORMAT = 'json';
	public const XML_FORMAT = 'xml';

	public const PACKAGE_NAME = 'phpGPX';
	public const VERSION = '1.3.0';

	/**
	 * Create Stats object for each track, segment and route
	 * @var bool
	 */
	public static $CALCULATE_STATS = true;

	/**
	 * Additional sort based on timestamp in Routes & Tracks on XML read.
	 * Disabled by default, data should be already sorted.
	 * @var bool
	 */
	public static $SORT_BY_TIMESTAMP = false;

	/**
	 * Default DateTime output format in JSON serialization.
	 * @var string
	 */
	public static $DATETIME_FORMAT = 'c';

	/**
	 * Default timezone for display.
	 * Data are always stored in UTC timezone.
	 * @var string
	 */
	public static $DATETIME_TIMEZONE_OUTPUT = 'UTC';

	/**
	 * Pretty print.
	 * @var bool
	 */
	public static $PRETTY_PRINT = true;

	/**
	 * In stats elevation calculation: ignore points with an elevation of 0
	 * This can happen with some GPS software adding a point with 0 elevation
	 *
	 * @var bool
	 */
	public static $IGNORE_ELEVATION_0 = true;

	/**
	 * Apply elevation gain/loss smoothing? If true, the threshold in
	 * ELEVATION_SMOOTHING_THRESHOLD and ELEVATION_SMOOTHING_SPIKES_THRESHOLD (if not null) applies
	 * @var bool
	 */
	public static $APPLY_ELEVATION_SMOOTHING = false;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the minimum elevation difference between considered points in meters
	 * @var int
	 */
	public static $ELEVATION_SMOOTHING_THRESHOLD = 2;

	/**
	 * if APPLY_ELEVATION_SMOOTHING is true
	 * the maximum elevation difference between considered points in meters
	 * @var int|null
	 */
	public static $ELEVATION_SMOOTHING_SPIKES_THRESHOLD;

	/**
	 * Apply distance calculation smoothing? If true, the threshold in
	 * DISTANCE_SMOOTHING_THRESHOLD applies
	 * @var bool
	 */
	public static $APPLY_DISTANCE_SMOOTHING = false;

	/**
	 * if APPLY_DISTANCE_SMOOTHING is true
	 * the minimum distance between considered points in meters
	 * @var int
	 */
	public static $DISTANCE_SMOOTHING_THRESHOLD = 2;

	/**
	 * Load GPX file.
	 * @param $path
	 * @return GpxFile
	 */
	public static function load($path)
	{
		$xml = file_get_contents($path);

		return self::parse($xml);
	}

	/**
  * Parse GPX data string.
  * @param $xml
  */
	public static function parse($xml): \phpGPX\Models\GpxFile
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
