<?php

declare(strict_types=1);
/**
 * Created            10/02/2017 15:44
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Route;
use phpGPX\phpGPX;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Route objects.
 *
 * This class provides static methods for converting between XML/SimpleXML
 * and Route model objects. It is not meant to be instantiated.
 *
 * @package phpGPX\Parsers
 * @psalm-api
 */
final class RouteParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 */
	private function __construct()
	{
	}

	public static string $tagName = 'rte';

	/** @var array<string, array{name: string, type: string}> */
	private static array $attributeMapper = [
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
		'links' => [
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
		'rtept' => [
			'name' => 'points',
			'type' => 'array',
		],
	];

	/**
	 * @param SimpleXMLElement|array<SimpleXMLElement> $nodes
	 * @return Route[]
	 */
	public static function parse(SimpleXMLElement|array $nodes): array
	{
		$routes = [];

		foreach ($nodes as $node) {
			$route = new Route();

			foreach (self::$attributeMapper as $key => $attribute) {
				switch ($key) {
					case 'link':
						$route->links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : [];
						break;
					case 'extensions':
						$route->extensions = property_exists($node, 'extensions') && $node->extensions !== null ? ExtensionParser::parse($node->extensions) : null;
						break;
					case 'rtept':
						$route->points = [];

						if (property_exists($node, 'rtept') && $node->rtept !== null) {
							foreach ($node->rtept as $point) {
								$parsedPoint = PointParser::parse($point);
								if ($parsedPoint instanceof \phpGPX\Models\Point) {
									$route->points[] = $parsedPoint;
								}
							}
						}

						break;
					default:
						if (!in_array($attribute['type'], ['object', 'array'], true)) {
							/** @var mixed $value */
							$value = $node->$key ?? null;
							if ($value !== null && ($value instanceof SimpleXMLElement || is_scalar($value))) {
								// Cast SimpleXMLElement to string first
								$stringValue = (string) $value;
								if ($stringValue !== '') {
									$type = $attribute['type'];
									settype($stringValue, $type);
									$route->{$attribute['name']} = $stringValue;
								}
							}
						}

						break;
				}
			}

			if (phpGPX::$CALCULATE_STATS) {
				$route->recalculateStats();
			}

			$routes[] = $route;
		}

		return $routes;
	}

	public static function toXML(Route $route, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node = $document->createElement($tagName);

		foreach (self::$attributeMapper as $key => $attribute) {
			if (!is_null($route->{$attribute['name']})) {
				$child = null;

				switch ($key) {
					case 'links':
						$child = LinkParser::toXMLArray($route->links, $document);
						break;
					case 'extensions':
						if ($route->extensions instanceof \phpGPX\Models\Extensions) {
							$child = ExtensionParser::toXML($route->extensions, $document);
						}

						break;
					case 'rtept':
						$child = PointParser::toXMLArray($route->points, $document);
						break;
					default:
						$child = $document->createElement($key);
						if ($child !== false) {
							/** @var mixed $value */
							$value = $route->{$attribute['name']};
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
	 * @param Route[] $routes
	 * @return DOMElement[]
	 * @internal Used internally for XML serialization
	 * @psalm-internal phpGPX\Parsers
	 */
	public static function toXMLArray(array $routes, DOMDocument &$document): array
	{
		$result = [];

		foreach ($routes as $route) {
			$result[] = self::toXML($route, $document);
		}

		return $result;
	}
}
