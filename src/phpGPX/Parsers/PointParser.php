<?php

declare(strict_types=1);
/**
 * Created            15/02/2017 18:14
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Enums\PointType;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\Models\Point;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Point objects.
 *
 * This class provides static methods for converting between XML/SimpleXML
 * and Point model objects. It is not meant to be instantiated.
 *
 * @package phpGPX\Parsers
 */
final class PointParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 */
	private function __construct()
	{
	}

	/** @var array<string, array{name: string, type: string}> */
	private static array $attributeMapper = [
		'ele' => [
			'name' => 'elevation',
			'type' => 'float',
		],
		'time' => [
			'name' => 'time',
			'type' => 'object',
		],
		'magvar' => [
			'name' => 'magVar',
			'type' => 'float',
		],
		'geoidheight' => [
			'name' => 'geoidHeight',
			'type' => 'float',
		],
		'name' => [
			'name' => 'name',
			'type' => 'string',
		],
		'cmt' => [
			'name' => 'comment',
			'type' => 'string',
		],
		'desc' => [
			'name' => 'description',
			'type' => 'string',
		],
		'src' => [
			'name' => 'source',
			'type' => 'string',
		],
		'link' => [
			'name' => 'links',
			'type' => 'object',
		],
		'sym' => [
			'name' => 'symbol',
			'type' => 'string',
		],
		'type' => [
			'name' => 'type',
			'type' => 'string',
		],
		'fix' => [
			'name' => 'fix',
			'type' => 'string',
		],
		'sat' => [
			'name' => 'satellitesNumber',
			'type' => 'integer',
		],
		'hdop' => [
			'name' => 'hdop',
			'type' => 'float',
		],
		'vdop' => [
			'name' => 'vdop',
			'type' => 'float',
		],
		'pdop' => [
			'name' => 'pdop',
			'type' => 'float',
		],
		'ageofdgpsdata' => [
			'name' => 'ageOfGpsData',
			'type' => 'float',
		],
		'dgpsid' => [
			'name' => 'dgpsid',
			'type' => 'integer',
		],
		'extensions' => [
			'name' => 'extensions',
			'type' => 'object',
		],
	];

	/** @var array<string, PointType> */
	private static array $typeMapper = [
		'trkpt' => PointType::TRACKPOINT,
		'wpt' => PointType::WAYPOINT,
		'rtept' => PointType::ROUTEPOINT,
	];

	public static function parse(SimpleXMLElement $node): ?Point
	{
		if (!array_key_exists($node->getName(), self::$typeMapper)) {
			return null;
		}

		// Latitude and longitude are required in GPX 1.1 spec
		if (!isset($node['lat']) || !isset($node['lon'])) {
			return null; // Invalid point without coordinates
		}

		$latitude = (float) $node['lat'];
		$longitude = (float) $node['lon'];

		$point = new Point(self::$typeMapper[$node->getName()], $latitude, $longitude);

		foreach (self::$attributeMapper as $key => $attribute) {
			switch ($key) {
				case 'time':
					$point->time = property_exists($node, 'time') && $node->time !== null ? DateTimeHelper::parseDateTime((string) $node->time) : null;
					break;
				case 'extensions':
					$point->extensions = property_exists($node, 'extensions') && $node->extensions !== null ? ExtensionParser::parse($node->extensions) : null;
					break;
				case 'link':
					$point->links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : [];
					break;
				default:
					if (!in_array($attribute['type'], ['object', 'array'], true)) {
						/** @var mixed $value */
						$value = $node->$key ?? null;
						if (!is_null($value) && ($value instanceof SimpleXMLElement || is_scalar($value))) {
							// Cast SimpleXMLElement to string first, then to proper type
							$stringValue = (string) $value;
							$point->{$attribute['name']} = match ($attribute['type']) {
								'float' => (float) $stringValue,
								'integer' => (int) $stringValue,
								'string' => $stringValue,
								default => $stringValue,
							};
						} else {
							$point->{$attribute['name']} = null;
						}
					}

					break;
			}
		}

		return $point;
	}

	public static function toXML(Point $point, DOMDocument &$document): DOMElement
	{
		// Use match expression to map PointType enum to XML element name
		$elementName = match ($point->getPointType()) {
			PointType::TRACKPOINT => 'trkpt',
			PointType::WAYPOINT => 'wpt',
			PointType::ROUTEPOINT => 'rtept',
		};
		$node = $document->createElement($elementName);

		$node->setAttribute('lat', (string) $point->latitude);
		$node->setAttribute('lon', (string) $point->longitude);

		foreach (self::$attributeMapper as $key => $attribute) {
			if (!is_null($point->{$attribute['name']})) {
				$child = null;

				switch ($key) {
					case 'link':
						$child = LinkParser::toXMLArray($point->links, $document);
						break;
					case 'time':
						$timeValue = DateTimeHelper::formatDateTime($point->time);
						if ($timeValue !== null) {
							$child = $document->createElement('time', $timeValue);
						}

						break;
					case 'extensions':
						if ($point->extensions instanceof \phpGPX\Models\Extensions) {
							$child = ExtensionParser::toXML($point->extensions, $document);
						}

						break;
					default:
						$child = $document->createElement($key);
						if ($child !== false) {
							/** @var mixed $value */
							$value = $point->{$attribute['name']};
							$stringValue = is_scalar($value) ? (string) $value : '';
							$elementText = $document->createTextNode($stringValue);
							$child->appendChild($elementText);
						}

						break;
				}

				if (is_array($child)) {
					foreach ($child as $item) {
						$node->appendChild($item);
					}
				} elseif ($child instanceof DOMElement) {
					$node->appendChild($child);
				}
			}
		}

		return $node;
	}

	/**
	 * @param Point[] $points
	 * @return DOMElement[]
	 */
	public static function toXMLArray(array $points, DOMDocument &$document): array
	{
		$result = [];

		foreach ($points as $point) {
			$result[] = self::toXML($point, $document);
		}

		return $result;
	}
}
