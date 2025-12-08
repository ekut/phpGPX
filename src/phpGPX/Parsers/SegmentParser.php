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
 * Class SegmentParser
 * @package phpGPX\Parsers
 */
abstract class SegmentParser
{
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

			if (!$node->count()) {
				continue;
			}

			if (isset($node->trkpt)) {
				$segment->points = [];

				foreach ($node->trkpt as $point) {
					$parsedPoint = PointParser::parse($point);
					if ($parsedPoint !== null) {
						$segment->points[] = $parsedPoint;
					}
				}
			}
			$segment->extensions = isset($node->extensions) ? ExtensionParser::parse($node->extensions) : null;

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
