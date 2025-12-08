<?php

declare(strict_types=1);
/**
 * Created            15/02/2017 18:29
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Extensions;
use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\Extensions\TrackPointExtensionParser;
use SimpleXMLElement;

/**
 * Class ExtensionParser
 * @package phpGPX\Parsers
 */
abstract class ExtensionParser
{
	public static string $tagName = 'extensions';

	/** @var array<string, array{namespace: string, xsd: string, name: string, prefix: string}> */
	public static array $usedNamespaces = [];

	public static function parse(SimpleXMLElement $nodes): Extensions
	{
		$extensions = new Extensions();

		$nodeNamespaces = $nodes->getNamespaces(true);

		foreach ($nodeNamespaces as $key => $namespace) {
			// Type guard: ensure key and namespace are strings
			if (!is_string($key)) {
				$key = '';
			}
			if (!is_string($namespace)) {
				continue;
			}
			
			switch ($namespace) {
				case TrackPointExtension::EXTENSION_NAMESPACE:
				case TrackPointExtension::EXTENSION_V1_NAMESPACE:
					$childNodes = $nodes->children($namespace);
					$node = $childNodes->{TrackPointExtension::EXTENSION_NAME};
					if ($node !== null && count($node) > 0) {
						$extensions->trackPointExtension = TrackPointExtensionParser::parse($node);
					}
					break;
				default:
					$childNodes = $nodes->children($namespace);
					// Null check before iterating
					if ($childNodes === null) {
						break;
					}
					// Iterate over child nodes
					foreach ($childNodes as $child_key => $value) {
						// Type guard: ensure child_key is string
						if (!is_string($child_key)) {
							continue;
						}
						$extensions->unsupported[$key ? "$key:$child_key" : "$child_key"] = (string) $value;
					}
			}
		}

		return $extensions;
	}

	public static function toXML(Extensions $extensions, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node =  $document->createElement($tagName);

		if (null !== $extensions->trackPointExtension) {
			$child = TrackPointExtensionParser::toXML($extensions->trackPointExtension, $document);
			$node->appendChild($child);
		}

		if (!empty($extensions->unsupported)) {
			foreach ($extensions->unsupported as $key => $value) {
				$child = $document->createElement($key, $value);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
