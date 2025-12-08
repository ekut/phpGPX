<?php

declare(strict_types=1);
/**
 * Created            10/02/2017 15:44
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use phpGPX\Models\Point;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Waypoint objects.
 * 
 * This class provides static methods for converting between XML/SimpleXML
 * and Waypoint model objects. It is not meant to be instantiated.
 * 
 * @package phpGPX\Parsers
 */
final class WaypointParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 */
	private function __construct()
	{
	}

	/**
	 * @param SimpleXMLElement $nodes - a non empty list of wpt elements
	 * @return Point[]
	 */
	public static function parse(SimpleXMLElement $nodes): array
	{
		$points = [];

		// foreach ($nodes->wpt as $item) this was incorrect, the ->wpt was already done in the caller
		foreach ($nodes as $item) {
			$point = PointParser::parse($item);

			if ($point) {
				$points[] = $point;
			}
		}

		return $points;
	}
}
