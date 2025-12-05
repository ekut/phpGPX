<?php

declare(strict_types=1);

/**
 * Created            30/08/16 17:27
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Helpers;

use phpGPX\Models\Point;

/**
 * Class GeoHelper
 * Geolocation methods.
 * @package phpGPX\Helpers
 */
abstract class GeoHelper
{
	public const float EARTH_RADIUS = 6371000.0;

	/**
	 * Returns distance in meters between two Points according to GPX coordinates.
	 * @see Point
	 */
	public static function getRawDistance(Point $point1, Point $point2): float
	{
		// PHPStan: latitude and longitude can be null, but deg2rad expects float
		// This is intentional - if coordinates are null, TypeError will be thrown
		// @phpstan-ignore argument.type
		$latFrom = deg2rad($point1->latitude);
		// @phpstan-ignore argument.type
		$lonFrom = deg2rad($point1->longitude);
		// @phpstan-ignore argument.type
		$latTo = deg2rad($point2->latitude);
		// @phpstan-ignore argument.type
		$lonTo = deg2rad($point2->longitude);

		$lonDelta = $lonTo - $lonFrom;
		$a = (cos($latTo) * sin($lonDelta)) ** 2 + (cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta)) ** 2;
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
		$angle = atan2(sqrt($a), $b);

		return $angle * self::EARTH_RADIUS;
	}

	/**
	 * Returns distance between two points including elevation gain/loss
	 */
	public static function getRealDistance(Point $point1, Point $point2): float
	{
		$distance = self::getRawDistance($point1, $point2);

		$elevation1 = $point1->elevation !== null ? $point1->elevation : 0;
		$elevation2 = $point2->elevation !== null ? $point2->elevation : 0;
		$elevDiff = abs($elevation1 - $elevation2);

		return sqrt($distance ** 2 + $elevDiff ** 2);
	}
}
