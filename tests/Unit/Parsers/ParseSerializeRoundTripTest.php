<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Parsers;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use phpGPX\Models\Bounds;
use phpGPX\Models\Extensions;
use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\PointParser;
use phpGPX\Parsers\BoundsParser;
use phpGPX\Parsers\ExtensionParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Property-based tests for parse-serialize round-trip consistency.
 * 
 * **Feature: test-coverage, Property 7: Parse-serialize round-trip**
 * **Validates: Requirements 3.6, 4.4**
 * 
 * This test verifies that for any valid GPX XML, parsing it to objects then
 * serializing back to XML should produce semantically equivalent GPX data.
 */
final class ParseSerializeRoundTripTest extends TestCase
{
	use TestTrait;

	/**
	 * Property test: Point parse-serialize round-trip.
	 * 
	 * For any valid Point XML, parsing then serializing should preserve all data.
	 */
	public function test_property_point_parse_serialize_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::choose(-1000, 9000), // elevation
				Generators::elements([Point::WAYPOINT, Point::TRACKPOINT, Point::ROUTEPOINT])
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $eleBase, $pointType) {
				// Convert to floats with decimal precision
				$latitude = (float) $latBase + (mt_rand(0, 999999) / 1000000);
				$longitude = (float) $lonBase + (mt_rand(0, 999999) / 1000000);
				$elevation = (float) $eleBase + (mt_rand(0, 999999) / 1000000);
				
				// Ensure within valid ranges
				$latitude = min(90.0, max(-90.0, $latitude));
				$longitude = min(180.0, max(-180.0, $longitude));
				
				// Determine element name based on point type
				$elementName = match($pointType) {
					Point::WAYPOINT => 'wpt',
					Point::TRACKPOINT => 'trkpt',
					Point::ROUTEPOINT => 'rtept',
					default => 'trkpt',
				};
				
				// Create XML string
				$xmlString = sprintf(
					'<?xml version="1.0"?><%s lat="%.10f" lon="%.10f"><ele>%.6f</ele></%s>',
					$elementName,
					$latitude,
					$longitude,
					$elevation,
					$elementName
				);
				
				// Parse XML
				$xml = simplexml_load_string($xmlString);
				$parsedPoint = PointParser::parse($xml);
				
				// Verify parsing succeeded
				$this->assertInstanceOf(Point::class, $parsedPoint, "Parsing should produce a Point object");
				
				// Serialize back to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = PointParser::toXML($parsedPoint, $document);
				$document->appendChild($xmlElement);
				
				// Parse the serialized XML again
				$simpleXml = simplexml_import_dom($xmlElement);
				$reparsedPoint = PointParser::parse($simpleXml);
				
				// Verify round-trip consistency
				$this->assertInstanceOf(Point::class, $reparsedPoint, "Re-parsing should produce a Point object");
				$this->assertEquals($pointType, $reparsedPoint->getPointType()->value, "Point type should be preserved");
				
				// Allow small floating point tolerance
				$epsilon = 0.000001;
				$this->assertEqualsWithDelta(
					$parsedPoint->latitude,
					$reparsedPoint->latitude,
					$epsilon,
					"Latitude should be preserved through parse-serialize round-trip"
				);
				$this->assertEqualsWithDelta(
					$parsedPoint->longitude,
					$reparsedPoint->longitude,
					$epsilon,
					"Longitude should be preserved through parse-serialize round-trip"
				);
				$this->assertEqualsWithDelta(
					$parsedPoint->elevation,
					$reparsedPoint->elevation,
					$epsilon,
					"Elevation should be preserved through parse-serialize round-trip"
				);
			});
	}

	/**
	 * Property test: Bounds parse-serialize round-trip.
	 * 
	 * For any valid Bounds XML, parsing then serializing should preserve all data.
	 */
	public function test_property_bounds_parse_serialize_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // minLat
				Generators::choose(-180, 180),  // minLon
				Generators::choose(-90, 90),    // maxLat
				Generators::choose(-180, 180)   // maxLon
			)
			->withMaxSize(100)
			->then(function ($minLatBase, $minLonBase, $maxLatBase, $maxLonBase) {
				// Convert to floats with decimal precision
				$minLat = (float) $minLatBase + (mt_rand(0, 999999) / 1000000);
				$minLon = (float) $minLonBase + (mt_rand(0, 999999) / 1000000);
				$maxLat = (float) $maxLatBase + (mt_rand(0, 999999) / 1000000);
				$maxLon = (float) $maxLonBase + (mt_rand(0, 999999) / 1000000);
				
				// Ensure within valid ranges
				$minLat = min(90.0, max(-90.0, $minLat));
				$minLon = min(180.0, max(-180.0, $minLon));
				$maxLat = min(90.0, max(-90.0, $maxLat));
				$maxLon = min(180.0, max(-180.0, $maxLon));
				
				// Ensure min < max
				if ($minLat > $maxLat) {
					[$minLat, $maxLat] = [$maxLat, $minLat];
				}
				if ($minLon > $maxLon) {
					[$minLon, $maxLon] = [$maxLon, $minLon];
				}
				
				// Create XML string
				$xmlString = sprintf(
					'<?xml version="1.0"?><bounds minlat="%.10f" minlon="%.10f" maxlat="%.10f" maxlon="%.10f"/>',
					$minLat,
					$minLon,
					$maxLat,
					$maxLon
				);
				
				// Parse XML
				$xml = simplexml_load_string($xmlString);
				$parsedBounds = BoundsParser::parse($xml);
				
				// Verify parsing succeeded
				$this->assertInstanceOf(Bounds::class, $parsedBounds, "Parsing should produce a Bounds object");
				
				// Serialize back to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = BoundsParser::toXML($parsedBounds, $document);
				$document->appendChild($xmlElement);
				
				// Parse the serialized XML again
				$simpleXml = simplexml_import_dom($xmlElement);
				$reparsedBounds = BoundsParser::parse($simpleXml);
				
				// Verify round-trip consistency
				$this->assertInstanceOf(Bounds::class, $reparsedBounds, "Re-parsing should produce a Bounds object");
				
				// Allow small floating point tolerance
				$epsilon = 0.000001;
				$this->assertEqualsWithDelta(
					$parsedBounds->minLatitude,
					$reparsedBounds->minLatitude,
					$epsilon,
					"Min latitude should be preserved through parse-serialize round-trip"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->minLongitude,
					$reparsedBounds->minLongitude,
					$epsilon,
					"Min longitude should be preserved through parse-serialize round-trip"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->maxLatitude,
					$reparsedBounds->maxLatitude,
					$epsilon,
					"Max latitude should be preserved through parse-serialize round-trip"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->maxLongitude,
					$reparsedBounds->maxLongitude,
					$epsilon,
					"Max longitude should be preserved through parse-serialize round-trip"
				);
			});
	}

	/**
	 * Property test: TrackPointExtension data preservation through serialization.
	 * 
	 * For any valid TrackPointExtension, the data should be preserved in the serialized form.
	 * Note: Full round-trip with namespace handling is complex due to SimpleXML limitations,
	 * so we verify that serialization preserves the data structure.
	 */
	public function test_property_trackpoint_extension_serialization_preserves_data(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-50, 50),    // temperature
				Generators::choose(60, 200),    // heart rate
				Generators::choose(50, 120),    // cadence
				Generators::choose(0, 20)       // speed
			)
			->withMaxSize(100)
			->then(function ($tempBase, $hrBase, $cadBase, $speedBase) {
				// Convert to floats with decimal precision
				$temp = (float) $tempBase + (mt_rand(0, 999999) / 1000000);
				$hr = (float) $hrBase;
				$cad = (float) $cadBase;
				$speed = (float) $speedBase + (mt_rand(0, 999999) / 1000000);
				
				// Create TrackPointExtension
				$tpe = new TrackPointExtension();
				$tpe->aTemp = $temp;
				$tpe->hr = $hr;
				$tpe->cad = $cad;
				$tpe->speed = $speed;
				
				$extensions = new Extensions();
				$extensions->trackPointExtension = $tpe;
				
				// Serialize to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = ExtensionParser::toXML($extensions, $document);
				$document->appendChild($xmlElement);
				
				// Get the XML string
				$xmlString = $document->saveXML();
				
				// Verify the XML contains the expected values
				$this->assertStringContainsString('TrackPointExtension', $xmlString, "XML should contain TrackPointExtension");
				$this->assertStringContainsString('atemp', $xmlString, "XML should contain atemp element");
				$this->assertStringContainsString('hr', $xmlString, "XML should contain hr element");
				$this->assertStringContainsString('cad', $xmlString, "XML should contain cad element");
				$this->assertStringContainsString('speed', $xmlString, "XML should contain speed element");
				
				// Verify the serialized values are present (with some tolerance for formatting)
				$this->assertStringContainsString(
					sprintf('%.0f', $hr),
					$xmlString,
					"XML should contain the heart rate value"
				);
				$this->assertStringContainsString(
					sprintf('%.0f', $cad),
					$xmlString,
					"XML should contain the cadence value"
				);
			});
	}

	/**
	 * Property test: Point with optional fields parse-serialize round-trip.
	 * 
	 * For any Point with optional fields, parsing then serializing should preserve all data.
	 */
	public function test_property_point_with_optional_fields_parse_serialize_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::string(),           // name
				Generators::string()            // description
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $name, $description) {
				// Convert to floats
				$latitude = (float) $latBase;
				$longitude = (float) $lonBase;
				
				// Escape XML special characters
				$name = htmlspecialchars($name, ENT_XML1, 'UTF-8');
				$description = htmlspecialchars($description, ENT_XML1, 'UTF-8');
				
				// Create XML string with optional fields
				$xmlString = sprintf(
					'<?xml version="1.0"?><trkpt lat="%.10f" lon="%.10f"><name>%s</name><desc>%s</desc></trkpt>',
					$latitude,
					$longitude,
					$name,
					$description
				);
				
				// Parse XML
				$xml = simplexml_load_string($xmlString);
				$parsedPoint = PointParser::parse($xml);
				
				// Verify parsing succeeded
				$this->assertInstanceOf(Point::class, $parsedPoint, "Parsing should produce a Point object");
				
				// Serialize back to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = PointParser::toXML($parsedPoint, $document);
				$document->appendChild($xmlElement);
				
				// Parse the serialized XML again
				$simpleXml = simplexml_import_dom($xmlElement);
				$reparsedPoint = PointParser::parse($simpleXml);
				
				// Verify round-trip consistency
				$this->assertInstanceOf(Point::class, $reparsedPoint, "Re-parsing should produce a Point object");
				$this->assertEquals(
					$parsedPoint->name,
					$reparsedPoint->name,
					"Name should be preserved through parse-serialize round-trip"
				);
				$this->assertEquals(
					$parsedPoint->description,
					$reparsedPoint->description,
					"Description should be preserved through parse-serialize round-trip"
				);
			});
	}

	/**
	 * Property test: Bounds with edge values parse-serialize round-trip.
	 * 
	 * For any Bounds including edge values, parsing then serializing should preserve all data.
	 */
	public function test_property_bounds_with_edge_values_parse_serialize_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([-90.0, -45.0, 0.0, 45.0, 90.0]),    // minLat
				Generators::elements([-180.0, -90.0, 0.0, 90.0, 180.0]),  // minLon
				Generators::elements([-90.0, -45.0, 0.0, 45.0, 90.0]),    // maxLat
				Generators::elements([-180.0, -90.0, 0.0, 90.0, 180.0])   // maxLon
			)
			->withMaxSize(100)
			->then(function ($minLat, $minLon, $maxLat, $maxLon) {
				// Ensure min < max
				if ($minLat > $maxLat) {
					[$minLat, $maxLat] = [$maxLat, $minLat];
				}
				if ($minLon > $maxLon) {
					[$minLon, $maxLon] = [$maxLon, $minLon];
				}
				
				// Create XML string
				$xmlString = sprintf(
					'<?xml version="1.0"?><bounds minlat="%.1f" minlon="%.1f" maxlat="%.1f" maxlon="%.1f"/>',
					$minLat,
					$minLon,
					$maxLat,
					$maxLon
				);
				
				// Parse XML
				$xml = simplexml_load_string($xmlString);
				$parsedBounds = BoundsParser::parse($xml);
				
				// Verify parsing succeeded
				$this->assertInstanceOf(Bounds::class, $parsedBounds, "Parsing should produce a Bounds object");
				
				// Serialize back to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = BoundsParser::toXML($parsedBounds, $document);
				$document->appendChild($xmlElement);
				
				// Parse the serialized XML again
				$simpleXml = simplexml_import_dom($xmlElement);
				$reparsedBounds = BoundsParser::parse($simpleXml);
				
				// Verify round-trip consistency
				$this->assertInstanceOf(Bounds::class, $reparsedBounds, "Re-parsing should produce a Bounds object");
				
				$epsilon = 0.01;
				$this->assertEqualsWithDelta(
					$parsedBounds->minLatitude,
					$reparsedBounds->minLatitude,
					$epsilon,
					"Min latitude should be preserved"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->minLongitude,
					$reparsedBounds->minLongitude,
					$epsilon,
					"Min longitude should be preserved"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->maxLatitude,
					$reparsedBounds->maxLatitude,
					$epsilon,
					"Max latitude should be preserved"
				);
				$this->assertEqualsWithDelta(
					$parsedBounds->maxLongitude,
					$reparsedBounds->maxLongitude,
					$epsilon,
					"Max longitude should be preserved"
				);
			});
	}
}
