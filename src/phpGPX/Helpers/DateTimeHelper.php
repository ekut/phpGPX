<?php

declare(strict_types=1);

/**
 * Created            05/09/16 17:02
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Helpers;

use DateTime;
use DateTimeZone;
use phpGPX\Models\Point;

/**
 * Class DateTimeHelper
 * @package phpGPX\Helpers
 */
class DateTimeHelper
{
	/**
	 * Compare two points by their timestamp for sorting.
	 * Returns negative if point1 < point2, zero if equal, positive if point1 > point2.
	 */
	public static function comparePointsByTimestamp(Point $point1, Point $point2): int
	{
		return $point1->time <=> $point2->time;
	}

	/**
	 * Format a DateTime object to string.
	 */
	public static function formatDateTime(?DateTime $datetime, string $format = 'c', string $timezone = 'UTC'): ?string
	{
		$formatted 				= null;

		if ($datetime instanceof DateTime) {
			$datetime->setTimezone(new DateTimeZone($timezone));
			$formatted 			= $datetime->format($format);
		}

		return $formatted;
	}

	/**
	 * Parse a string value to DateTime object.
	 */
	public static function parseDateTime(string $value, string $timezone = 'Europe/London'): DateTime
	{
		$timezone = new DateTimeZone($timezone);
		$datetime = new DateTime($value, $timezone);
		$datetime->setTimezone(new DateTimeZone(date_default_timezone_get()));

		return $datetime;
	}
}
