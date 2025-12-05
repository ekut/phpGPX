<?php
/**
 * Created            16/02/2017 22:09
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use phpGPX\Models\Bounds;

/**
 * Class BoundsParser
 * @package phpGPX\Parsers
 */
abstract class BoundsParser
{
	private static $tagName = 'bounds';

	/**
	 * Parse data from XML.
	 * 
	 * All four coordinate attributes are required per GPX 1.1 schema.
	 * Returns null if any required attribute is missing.
	 * 
	 * @return Bounds|null
	 */
	public static function parse(\SimpleXMLElement $node)
	{
		if ($node->getName() != self::$tagName) {
			return null;
		}

		// All four attributes are required per GPX 1.1 schema
		if (!isset($node['minlat']) || !isset($node['minlon']) || 
			!isset($node['maxlat']) || !isset($node['maxlon'])) {
			return null;
		}

		return new Bounds(
			(float) $node['minlat'],
			(float) $node['minlon'],
			(float) $node['maxlat'],
			(float) $node['maxlon']
		);
	}

	/**
	 * Create XML representation.
	 * 
	 * All four coordinate attributes are always present per GPX 1.1 schema.
	 * 
	 * @return \DOMElement
	 */
	public static function toXML(Bounds $bounds, \DOMDocument &$document)
	{
		$node = $document->createElement(self::$tagName);

		// All four attributes are required per GPX 1.1 schema
		$node->setAttribute('minlat', $bounds->minLatitude);
		$node->setAttribute('minlon', $bounds->minLongitude);
		$node->setAttribute('maxlat', $bounds->maxLatitude);
		$node->setAttribute('maxlon', $bounds->maxLongitude);

		return $node;
	}
}
