<?php

declare(strict_types=1);
/**
 * Created            30/08/16 13:31
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Track;
use phpGPX\phpGPX;
use SimpleXMLElement;

/**
 * Class TrackParser
 * @package phpGPX\Parsers
 */
abstract class TrackParser
{
	public static $tagName = 'trk';

	private static $attributeMapper = [
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
			'type' => 'array',
		],
		'number' => [
			'name' => 'number',
			'type' => 'integer',
		],
		'type' => [
			'name' => 'type',
			'type' => 'string',
		],
		'extensions' => [
			'name' => 'extensions',
			'type' => 'object',
		],
		'trkseg' => [
			'name' => 'segments',
			'type' => 'array',
		],
	];

	/**
  * @return Track[]
  */
	public static function parse(SimpleXMLElement $nodes): array
	{
		$tracks = [];

		foreach ($nodes as $node) {
			$track = new Track();

			foreach (self::$attributeMapper as $key => $attribute) {
				switch ($key) {
					case 'link':
						$track->links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : [];
						break;
					case 'extensions':
						$track->extensions = property_exists($node, 'extensions') && $node->extensions !== null ? ExtensionParser::parse($node->extensions) : null;
						break;
					case 'trkseg':
						$track->segments = property_exists($node, 'trkseg') && $node->trkseg !== null ? SegmentParser::parse($node->trkseg) : [];
						break;
					default:
						if (!in_array($attribute['type'], ['object', 'array'], true)) {
							$value = $node->$key ?? null;
							if ($value !== null) {
								// @phpstan-ignore cast.string
								$value = (string) $value;
								if ($value !== '') {
									settype($value, $attribute['type']);
									$track->{$attribute['name']} = $value;
								}
							}
						}
						break;
				}
			}

			if (phpGPX::$CALCULATE_STATS) {
				$track->recalculateStats();
			}

			$tracks[] = $track;
		}

		return $tracks;
	}

	public static function toXML(Track $track, DOMDocument &$document): DOMElement
	{
		$node = $document->createElement(self::$tagName);

		foreach (self::$attributeMapper as $key => $attribute) {
			if (!is_null($track->{$attribute['name']})) {
				switch ($key) {
					case 'link':
						$child = LinkParser::toXMLArray($track->links, $document);
						break;
					case 'extensions':
						$child = ExtensionParser::toXML($track->extensions, $document);
						break;
					case 'trkseg':
						$child = SegmentParser::toXMLArray($track->segments, $document);
						break;
					default:
						$child = $document->createElement($key);
						if ($child !== false) {
							$elementText = $document->createTextNode((string) $track->{$attribute['name']});
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
  * @return DOMElement[]
  */
	public static function toXMLArray(array $tracks, DOMDocument &$document): array
	{
		$result = [];

		foreach ($tracks as $track) {
			$result[] = self::toXML($track, $document);
		}

		return $result;
	}
}
