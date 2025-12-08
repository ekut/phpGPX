<?php

declare(strict_types=1);
/**
 * Created            16/02/2017 22:09
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Bounds;
use SimpleXMLElement;

/**
 * Class BoundsParser
 * @package phpGPX\Parsers
 */
abstract class BoundsParser
{
	private static string $tagName = 'bounds';

	/**
	 * Parse data from XML.
	 *
	 * All four coordinate attributes are required per GPX 1.1 schema.
	 * Returns null if any required attribute is missing.
	 */
	public static function parse(SimpleXMLElement $node): ?Bounds
	{
		if ($node->getName() !== self::$tagName) {
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
			(float) $node['maxlon'],
		);
	}

	/**
	 * Create XML representation.
	 *
	 * All four coordinate attributes are always present per GPX 1.1 schema.
	 */
	public static function toXML(Bounds $bounds, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node = $document->createElement($tagName);

		// All four attributes are required per GPX 1.1 schema
		$node->setAttribute('minlat', (string) $bounds->minLatitude);
		$node->setAttribute('minlon', (string) $bounds->minLongitude);
		$node->setAttribute('maxlat', (string) $bounds->maxLatitude);
		$node->setAttribute('maxlon', (string) $bounds->maxLongitude);

		return $node;
	}
}
