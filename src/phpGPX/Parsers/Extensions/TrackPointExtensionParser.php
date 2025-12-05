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

class TrackPointExtensionParser
{
	private static $attributeMapper = [
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

			if ($value !== null) {
				// Cast to the appropriate type
				if ($attribute['type'] === 'float') {
					$extension->{$attribute['name']} = (float) $value;
				} elseif ($attribute['type'] === 'int') {
					$extension->{$attribute['name']} = (int) $value;
				}
			}

			// Remove in v1.0
			if ($key === 'hr') {
				$extension->heartRate = $extension->hr;
			}

			// Remove in v1.0
			if ($key === 'cad') {
				$extension->cadence = $extension->cad;
			}

			// Remove in v1.0
			if ($key === 'atemp') {
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
			if (!is_null($extension->{$attribute['name']})) {
				$child = $document->createElement(
					sprintf("%s:%s", TrackPointExtension::EXTENSION_NAMESPACE_PREFIX, $key),
					(string) $extension->{$attribute['name']},
				);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
