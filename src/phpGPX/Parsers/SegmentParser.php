<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 19:29
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Segment;
use phpGPX\phpGPX;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Segment objects.
 *
 * This class provides static methods for converting between XML/SimpleXML
 * and Segment model objects. It is not meant to be instantiated.
 *
 * @package phpGPX\Parsers
 */
final class SegmentParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 */
	private function __construct()
	{
	}

	public static string $tagName = 'trkseg';

	/**
	 * @param SimpleXMLElement|array<SimpleXMLElement> $nodes
	 * @return Segment[]
	 */
	public static function parse(SimpleXMLElement|array $nodes): array
	{
		$segments = [];

		foreach ($nodes as $node) {
			$segment = new Segment();

			if ($node->count() === 0) {
				continue;
			}

			if (property_exists($node, 'trkpt') && $node->trkpt !== null) {
				$segment->points = [];

				foreach ($node->trkpt as $point) {
					$parsedPoint = PointParser::parse($point);
					if ($parsedPoint instanceof \phpGPX\Models\Point) {
						$segment->points[] = $parsedPoint;
					}
				}
			}

			$segment->extensions = property_exists($node, 'extensions') && $node->extensions !== null ? ExtensionParser::parse($node->extensions) : null;

			if (phpGPX::$CALCULATE_STATS) {
				$segment->recalculateStats();
			}

			$segments[] = $segment;
		}

		return $segments;
	}

	public static function toXML(Segment $segment, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node = $document->createElement($tagName);

		foreach ($segment->points as $point) {
			$node->appendChild(PointParser::toXML($point, $document));
		}

		if (!empty($segment->extensions)) {
			$node->appendChild(ExtensionParser::toXML($segment->extensions, $document));
		}

		return $node;
	}

	/**
	 * @param Segment[] $segments
	 * @return DOMElement[]
	 */
	public static function toXMLArray(array $segments, DOMDocument $document): array
	{
		$result = [];

		foreach ($segments as $segment) {
			$result[] = self::toXML($segment, $document);
		}

		return $result;
	}
}
