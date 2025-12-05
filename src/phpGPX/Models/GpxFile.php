<?php

declare(strict_types=1);
/**
 * Created            17/02/2017 17:46
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use DOMDocument;
use InvalidArgumentException;
use phpGPX\Enums\FileFormat;
use phpGPX\Helpers\GpxValidator;
use phpGPX\Helpers\SerializationHelper;
use phpGPX\Parsers\ExtensionParser;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Parsers\PointParser;
use phpGPX\Parsers\RouteParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\phpGPX;

/**
 * Class GpxFile
 * Representation of GPX file.
 * GPX documents contain a metadata header, followed by waypoints, routes, and tracks.
 * @see https://www.topografix.com/GPX/1/1/#type_gpxType
 * @package phpGPX\Models
 */
class GpxFile implements Summarizable
{
	/**
	 * A list of waypoints.
	 * @var Point[]
	 */
	public $waypoints = [];

	/**
	 * A list of routes.
	 * @var Route[]
	 */
	public $routes = [];

	/**
	 * A list of tracks.
	 * @var Track[]
	 */
	public $tracks = [];

	/**
	 * Metadata about the file.
	 * The original GPX 1.1 attribute.
	 * @var Metadata|null
	 */
	public $metadata;

	/**
	 * @var Extensions|null
	 */
	public $extensions;

	/**
	 * Creator of GPX file.
	 * Required by GPX 1.1 schema.
	 * @var string
	 */
	public $creator;

	/**
	 * Create a new GpxFile instance.
	 *
	 * @param string $creator The creator of the GPX file (required by GPX 1.1 schema)
	 * @throws InvalidArgumentException If creator is empty or whitespace-only
	 */
	public function __construct(string $creator)
	{
		GpxValidator::validateNonEmptyString($creator, 'GPX creator');
		$this->creator = $creator;
	}

	/**
	 * Serialize object to array
	 */
	public function toArray(): array
	{
		return SerializationHelper::filterNotNull([
			'creator' => SerializationHelper::stringOrNull($this->creator),
			'metadata' => SerializationHelper::serialize($this->metadata),
			'waypoints' => SerializationHelper::serialize($this->waypoints),
			'routes' => SerializationHelper::serialize($this->routes),
			'tracks' => SerializationHelper::serialize($this->tracks),
			'extensions' => SerializationHelper::serialize($this->extensions),
		]);
	}

	/**
	 * Return JSON representation of GPX file with statistics.
	 * @return string
	 */
	public function toJSON()
	{
		return json_encode($this->toArray(), phpGPX::$PRETTY_PRINT ? JSON_PRETTY_PRINT : null);
	}

	/**
	 * Create XML representation of GPX file.
	 * @return DOMDocument
	 */
	public function toXML()
	{
		$document = new DOMDocument("1.0", 'UTF-8');

		$gpx = $document->createElementNS("http://www.topografix.com/GPX/1/1", "gpx");
		$gpx->setAttribute("version", "1.1");
		$gpx->setAttribute("creator", $this->creator);

		ExtensionParser::$usedNamespaces = [];

		if (!empty($this->metadata)) {
			$gpx->appendChild(MetadataParser::toXML($this->metadata, $document));
		}

		foreach ($this->waypoints as $waypoint) {
			$gpx->appendChild(PointParser::toXML($waypoint, $document));
		}

		foreach ($this->routes as $route) {
			$gpx->appendChild(RouteParser::toXML($route, $document));
		}

		foreach ($this->tracks as $track) {
			$gpx->appendChild(TrackParser::toXML($track, $document));
		}

		if (!empty($this->extensions)) {
			$gpx->appendChild(ExtensionParser::toXML($this->extensions, $document));
		}

		// Namespaces
		$schemaLocationArray = [
			'http://www.topografix.com/GPX/1/1',
			'http://www.topografix.com/GPX/1/1/gpx.xsd',
		];

		foreach (ExtensionParser::$usedNamespaces as $usedNamespace) {
			$gpx->setAttributeNS(
				"http://www.w3.org/2000/xmlns/",
				sprintf("xmlns:%s", $usedNamespace['prefix']),
				$usedNamespace['namespace'],
			);

			$schemaLocationArray[] = $usedNamespace['namespace'];
			$schemaLocationArray[] = $usedNamespace['xsd'];
		}

		$gpx->setAttributeNS(
			'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation',
			implode(" ", $schemaLocationArray),
		);

		$document->appendChild($gpx);

		if (phpGPX::$PRETTY_PRINT) {
			$document->formatOutput = true;
			$document->preserveWhiteSpace = true;
		}

		return $document;
	}

	/**
	 * Save data to file according to selected format.
	 * @param string $path
	 * @param FileFormat $format
	 */
	public function save(string $path, FileFormat $format): void
	{
		match ($format) {
			FileFormat::XML => $this->toXML()->save($path),
			FileFormat::JSON => file_put_contents($path, $this->toJSON()),
		};
	}
}
