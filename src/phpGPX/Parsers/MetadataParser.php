<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 15:58
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\Models\Metadata;
use SimpleXMLElement;

/**
 * Class MetadataParser
 * @package phpGPX\Parsers
 */
abstract class MetadataParser
{
	private static string $tagName = 'metadata';

	/** @var array<string, array{name: string, type: string}> */
	private static array $attributeMapper = [
		'name' => [
			'name' => 'name',
			'type' => 'string',
		],
		'desc' => [
			'name' => 'description',
			'type' => 'string',
		],
		'author' => [
			'name' => 'author',
			'type' => 'object',
		],
		'copyright' => [
			'name' => 'copyright',
			'type' => 'object',
		],
		'link' => [
			'name' => 'links',
			'type' => 'array',
		],
		'time' => [
			'name' => 'time',
			'type' => 'object',
		],
		'keywords' => [
			'name' => 'keywords',
			'type' => 'string',
		],
		'bounds' => [
			'name' => 'bounds',
			'type' => 'object',
		],
		'extensions' => [
			'name' => 'extensions',
			'type' => 'object',
		],
	];

	public static function parse(SimpleXMLElement $node): Metadata
	{
		$metadata = new Metadata();

		foreach (self::$attributeMapper as $key => $attribute) {
			switch ($key) {
				case 'author':
					$metadata->author = property_exists($node, 'author') && $node->author !== null ? PersonParser::parse($node->author) : null;
					break;
				case 'copyright':
					$metadata->copyright = property_exists($node, 'copyright') && $node->copyright !== null ? CopyrightParser::parse($node->copyright) : null;
					break;
				case 'link':
					$metadata->links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : null;
					break;
				case 'time':
					$metadata->time = property_exists($node, 'time') && $node->time !== null ? DateTimeHelper::parseDateTime((string) $node->time) : null;
					break;
				case 'bounds':
					$metadata->bounds = property_exists($node, 'bounds') && $node->bounds !== null ? BoundsParser::parse($node->bounds) : null;
					break;
				case 'extensions':
					$metadata->extensions = property_exists($node, 'extensions') && $node->extensions !== null ? ExtensionParser::parse($node->extensions) : null;
					break;
				default:
					if (!in_array($attribute['type'], ['object', 'array'], true)) {
						/** @var mixed $value */
						$value = $node->$key ?? null;
						if ($value !== null && ($value instanceof SimpleXMLElement || is_scalar($value))) {
							// Cast SimpleXMLElement to string first
							$stringValue = (string) $value;
							$type = $attribute['type'];
							assert(is_string($type));
							settype($stringValue, $type);
							$metadata->{$attribute['name']} = $stringValue;
						} else {
							$metadata->{$attribute['name']} = null;
						}
					}
					break;
			}
		}

		return $metadata;
	}

	public static function toXML(Metadata $metadata, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node =  $document->createElement($tagName);

		foreach (self::$attributeMapper as $key => $attribute) {
			if (!is_null($metadata->{$attribute['name']})) {
				$child = null;
				
				switch ($key) {
					case 'author':
						if ($metadata->author !== null) {
							$child = PersonParser::toXML($metadata->author, $document);
						}
						break;
					case 'copyright':
						if ($metadata->copyright !== null) {
							$child = CopyrightParser::toXML($metadata->copyright, $document);
						}
						break;
					case 'link':
						if ($metadata->links !== null) {
							$child = LinkParser::toXMLArray($metadata->links, $document);
						}
						break;
					case 'time':
						$timeValue = DateTimeHelper::formatDateTime($metadata->time);
						if ($timeValue !== null) {
							$child = $document->createElement('time', $timeValue);
						}
						break;
					case 'bounds':
						if ($metadata->bounds !== null) {
							$child = BoundsParser::toXML($metadata->bounds, $document);
						}
						break;
					case 'extensions':
						if ($metadata->extensions !== null) {
							$child = ExtensionParser::toXML($metadata->extensions, $document);
						}
						break;
					default:
						$child = $document->createElement($key);
						if ($child !== false) {
							/** @var mixed $value */
							$value = $metadata->{$attribute['name']};
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
}
