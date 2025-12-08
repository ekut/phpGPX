<?php

declare(strict_types=1);
/**
 * Created            16/02/2017 16:32
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers\Extensions;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\ExtensionParser;
use SimpleXMLElement;

final class TrackPointExtensionParser
{
	/** @var array<string, array{name: string, type: string}> */
	private static array $attributeMapper = [
		'atemp' => [
			'name' => 'aTemp',
			'type' => 'float',
		],
		'wtemp' => [
			'name' => 'wTemp',
			'type' => 'float',
		],
		'depth' => [
			'name' => 'depth',
			'type' => 'float',
		],
		'hr' => [
			'name' => 'hr',
			'type' => 'float',
		],
		'cad' => [
			'name' => 'cad',
			'type' => 'float',
		],
		'speed' => [
			'name' => 'speed',
			'type' => 'float',
		],
		'course' => [
			'name' => 'course',
			'type' => 'int',
		],
		'bearing' => [
			'name' => 'bearing',
			'type' => 'int',
		],
	];

	public static function parse(SimpleXMLElement $node): TrackPointExtension
	{
		$extension = new TrackPointExtension();

		foreach (self::$attributeMapper as $key => $attribute) {
			$value = $node->$key ?? null;

			if ($value !== null && ($value instanceof SimpleXMLElement || is_scalar($value))) {
				// Cast to string first, then to the appropriate type
				$stringValue = (string) $value;
				
				// Type guard: ensure attribute has expected structure
				if (!isset($attribute['type']) || !isset($attribute['name'])) {
					continue;
				}
				
				$attributeType = $attribute['type'];
				$attributeName = $attribute['name'];
				
				if ($attributeType === 'float') {
					$extension->{$attributeName} = (float) $stringValue;
				} elseif ($attributeType === 'int') {
					$extension->{$attributeName} = (int) $stringValue;
				}
			}

			// Maintain backward compatibility with deprecated properties
			// These assignments will be removed in v1.0
			if ($key === 'hr' && isset($extension->hr)) {
				$extension->heartRate = $extension->hr;
			}

			if ($key === 'cad' && isset($extension->cad)) {
				$extension->cadence = $extension->cad;
			}

			if ($key === 'atemp' && isset($extension->aTemp)) {
				$extension->avgTemperature = $extension->aTemp;
			}
		}

		return $extension;
	}

	public static function toXML(TrackPointExtension $extension, DOMDocument &$document): DOMElement
	{
		$node =  $document->createElement("gpxtpx:TrackPointExtension");

		ExtensionParser::$usedNamespaces[TrackPointExtension::EXTENSION_NAME] = [
			'namespace' => TrackPointExtension::EXTENSION_NAMESPACE,
			'xsd' => TrackPointExtension::EXTENSION_NAMESPACE_XSD,
			'name' => TrackPointExtension::EXTENSION_NAME,
			'prefix' => TrackPointExtension::EXTENSION_NAMESPACE_PREFIX,
		];

		foreach (self::$attributeMapper as $key => $attribute) {
			/** @var mixed $value */
			$value = $extension->{$attribute['name']};
			if (!is_null($value)) {
				// Ensure value is converted to string properly
				$stringValue = is_scalar($value) ? (string) $value : '';
				$child = $document->createElement(
					sprintf("%s:%s", TrackPointExtension::EXTENSION_NAMESPACE_PREFIX, $key),
					$stringValue,
				);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
