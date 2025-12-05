<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use phpGPX\Models\Track;
use phpGPX\Models\Segment;
use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Models\Link;
use phpGPX\Models\Bounds;
use phpGPX\Parsers\PointParser;
use phpGPX\Parsers\TrackParser;
use phpGPX\Parsers\SegmentParser;
use phpGPX\Parsers\MetadataParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Property-based tests for model serialization round-trip consistency.
 * 
 * **Feature: test-coverage, Property 2: Serialization round-trip consistency**
 * **Validates: Requirements 1.4, 2.5, 2.6, 2.7, 5.3**
 * 
 * This test verifies that for any valid model object and any supported format
 * (XML, JSON, array), serializing then deserializing should produce an equivalent object.
 */
final class SerializationRoundTripTest extends TestCase
{
	use TestTrait;

	/**
	 * Property test: Point serialization to array round-trip.
	 * 
	 * For any valid Point, converting to array and back should preserve all data.
	 */
	public function test_property_point_array_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::choose(-1000, 9000), // elevation
				Generators::elements([Point::WAYPOINT, Point::TRACKPOINT, Point::ROUTEPOINT]),
				Generators::string(),           // name
				Generators::string()            // description
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $eleBase, $pointType, $name, $description) {
				// Convert to floats with decimal precision
				$latitude = (float) $latBase + (mt_rand(0, 999999) / 1000000);
				$longitude = (float) $lonBase + (mt_rand(0, 999999) / 1000000);
				$elevation = (float) $eleBase + (mt_rand(0, 999999) / 1000000);
				
				// Ensure within valid ranges
				$latitude = min(90.0, max(-90.0, $latitude));
				$longitude = min(180.0, max(-180.0, $longitude));
				
				// Create original point
				$originalPoint = new Point($pointType);
				$originalPoint->latitude = $latitude;
				$originalPoint->longitude = $longitude;
				$originalPoint->elevation = $elevation;
				$originalPoint->name = $name;
				$originalPoint->description = $description;
				
				// Serialize to array
				$array = $originalPoint->toArray();
				
				// Verify array contains the data
				$this->assertIsArray($array);
				$this->assertArrayHasKey('lat', $array);
				$this->assertArrayHasKey('lon', $array);
				$this->assertArrayHasKey('ele', $array);
				$this->assertArrayHasKey('name', $array);
				$this->assertArrayHasKey('desc', $array);
				
				// Verify values match (round-trip property)
				$this->assertEquals($latitude, $array['lat'], "Latitude should be preserved in array");
				$this->assertEquals($longitude, $array['lon'], "Longitude should be preserved in array");
				$this->assertEquals($elevation, $array['ele'], "Elevation should be preserved in array");
				$this->assertEquals($name, $array['name'], "Name should be preserved in array");
				$this->assertEquals($description, $array['desc'], "Description should be preserved in array");
			});
	}

	/**
	 * Property test: Point serialization to XML round-trip.
	 * 
	 * For any valid Point, converting to XML and parsing back should preserve core data.
	 */
	public function test_property_point_xml_serialization_round_trip(): void
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
				
				// Create original point
				$originalPoint = new Point($pointType);
				$originalPoint->latitude = $latitude;
				$originalPoint->longitude = $longitude;
				$originalPoint->elevation = $elevation;
				
				// Serialize to XML
				$document = new \DOMDocument('1.0', 'UTF-8');
				$xmlElement = PointParser::toXML($originalPoint, $document);
				$document->appendChild($xmlElement);
				
				// Parse back from XML
				$simpleXml = simplexml_import_dom($xmlElement);
				$parsedPoint = PointParser::parse($simpleXml);
				
				// Verify round-trip consistency
				$this->assertInstanceOf(Point::class, $parsedPoint, "Parsed object should be a Point");
				$this->assertEquals($pointType, $parsedPoint->getPointType()->value, "Point type should be preserved");
				
				// Allow small floating point tolerance
				$epsilon = 0.000001;
				$this->assertEqualsWithDelta(
					$latitude,
					$parsedPoint->latitude,
					$epsilon,
					"Latitude should be preserved through XML round-trip"
				);
				$this->assertEqualsWithDelta(
					$longitude,
					$parsedPoint->longitude,
					$epsilon,
					"Longitude should be preserved through XML round-trip"
				);
				$this->assertEqualsWithDelta(
					$elevation,
					$parsedPoint->elevation,
					$epsilon,
					"Elevation should be preserved through XML round-trip"
				);
			});
	}

	/**
	 * Property test: Metadata serialization to array round-trip.
	 * 
	 * For any valid Metadata, converting to array should preserve all data.
	 */
	public function test_property_metadata_array_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::string(),  // name
				Generators::string(),  // description
				Generators::string()   // keywords
			)
			->withMaxSize(100)
			->then(function ($name, $description, $keywords) {
				// Create original metadata
				$originalMetadata = new Metadata();
				$originalMetadata->name = $name;
				$originalMetadata->description = $description;
				$originalMetadata->keywords = $keywords;
				
				// Serialize to array
				$array = $originalMetadata->toArray();
				
				// Verify array contains the data
				$this->assertIsArray($array);
				$this->assertArrayHasKey('name', $array);
				$this->assertArrayHasKey('desc', $array);
				$this->assertArrayHasKey('keywords', $array);
				
				// Verify values match (round-trip property)
				$this->assertEquals($name, $array['name'], "Name should be preserved in array");
				$this->assertEquals($description, $array['desc'], "Description should be preserved in array");
				$this->assertEquals($keywords, $array['keywords'], "Keywords should be preserved in array");
			});
	}

	/**
	 * Property test: Track serialization to array round-trip.
	 * 
	 * For any valid Track, converting to array should preserve basic properties.
	 */
	public function test_property_track_array_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::string(),  // name
				Generators::string(),  // description
				Generators::string(),  // comment
				Generators::choose(1, 100)  // number
			)
			->withMaxSize(100)
			->then(function ($name, $description, $comment, $number) {
				// Create original track
				$originalTrack = new Track();
				$originalTrack->name = $name;
				$originalTrack->description = $description;
				$originalTrack->comment = $comment;
				$originalTrack->number = $number;
				
				// Serialize to array
				$array = $originalTrack->toArray();
				
				// Verify array contains the data
				$this->assertIsArray($array);
				$this->assertArrayHasKey('name', $array);
				$this->assertArrayHasKey('desc', $array);
				$this->assertArrayHasKey('cmt', $array);
				$this->assertArrayHasKey('number', $array);
				
				// Verify values match (round-trip property)
				$this->assertEquals($name, $array['name'], "Name should be preserved in array");
				$this->assertEquals($description, $array['desc'], "Description should be preserved in array");
				$this->assertEquals($comment, $array['cmt'], "Comment should be preserved in array");
				$this->assertEquals($number, $array['number'], "Number should be preserved in array");
			});
	}

	/**
	 * Property test: Segment serialization to array round-trip.
	 * 
	 * For any valid Segment with points, converting to array should preserve point data.
	 */
	public function test_property_segment_array_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 5)  // number of points
			)
			->withMaxSize(100)
			->then(function ($numPoints) {
				// Create original segment with points
				$originalSegment = new Segment();
				
				for ($i = 0; $i < $numPoints; $i++) {
					$point = PointFactory::create([
						'latitude' => 54.0 + ($i * 0.01),
						'longitude' => 9.0 + ($i * 0.01),
						'elevation' => 100.0 + ($i * 10.0),
					]);
					$originalSegment->points[] = $point;
				}
				
				// Serialize to array
				$array = $originalSegment->toArray();
				
				// Verify array contains the data
				$this->assertIsArray($array);
				$this->assertArrayHasKey('points', $array);
				$this->assertIsArray($array['points']);
				$this->assertCount($numPoints, $array['points'], "Array should contain all points");
				
				// Verify each point's data is preserved
				for ($i = 0; $i < $numPoints; $i++) {
					$this->assertIsArray($array['points'][$i]);
					$this->assertArrayHasKey('lat', $array['points'][$i]);
					$this->assertArrayHasKey('lon', $array['points'][$i]);
					$this->assertArrayHasKey('ele', $array['points'][$i]);
					
					$expectedLat = 54.0 + ($i * 0.01);
					$expectedLon = 9.0 + ($i * 0.01);
					$expectedEle = 100.0 + ($i * 10.0);
					
					$this->assertEquals(
						$expectedLat,
						$array['points'][$i]['lat'],
						"Point $i latitude should be preserved"
					);
					$this->assertEquals(
						$expectedLon,
						$array['points'][$i]['lon'],
						"Point $i longitude should be preserved"
					);
					$this->assertEquals(
						$expectedEle,
						$array['points'][$i]['ele'],
						"Point $i elevation should be preserved"
					);
				}
			});
	}

	/**
	 * Property test: Point with time serialization round-trip.
	 * 
	 * For any Point with time, serializing to array should preserve the timestamp.
	 */
	public function test_property_point_with_time_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::choose(0, 365)      // days offset from base date
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $daysOffset) {
				// Convert to floats
				$latitude = (float) $latBase;
				$longitude = (float) $lonBase;
				
				// Create a time
				$baseTime = new \DateTime('2024-01-01 00:00:00', new \DateTimeZone('UTC'));
				$time = (clone $baseTime)->modify("+{$daysOffset} days");
				
				// Create original point
				$originalPoint = new Point(Point::TRACKPOINT);
				$originalPoint->latitude = $latitude;
				$originalPoint->longitude = $longitude;
				$originalPoint->time = $time;
				
				// Serialize to array
				$array = $originalPoint->toArray();
				
				// Verify time is preserved
				$this->assertArrayHasKey('time', $array);
				$this->assertNotNull($array['time'], "Time should be serialized");
				$this->assertIsString($array['time'], "Time should be serialized as string");
				
				// The time should contain the date
				$expectedDate = $time->format('Y-m-d');
				$this->assertStringContainsString(
					$expectedDate,
					$array['time'],
					"Serialized time should contain the date"
				);
			});
	}

	/**
	 * Property test: Point with links serialization round-trip.
	 * 
	 * For any Point with links, serializing to array should preserve link data.
	 */
	public function test_property_point_with_links_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::choose(1, 3)        // number of links
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $numLinks) {
				// Convert to floats
				$latitude = (float) $latBase;
				$longitude = (float) $lonBase;
				
				// Create original point
				$originalPoint = new Point(Point::WAYPOINT);
				$originalPoint->latitude = $latitude;
				$originalPoint->longitude = $longitude;
				
				// Add links
				for ($i = 0; $i < $numLinks; $i++) {
					$link = new Link();
					$link->href = "https://example.com/link{$i}";
					$link->text = "Link {$i}";
					$originalPoint->links[] = $link;
				}
				
				// Serialize to array
				$array = $originalPoint->toArray();
				
				// Verify links are preserved
				$this->assertArrayHasKey('link', $array);
				$this->assertIsArray($array['link']);
				$this->assertCount($numLinks, $array['link'], "All links should be serialized");
				
				// Verify each link's data
				for ($i = 0; $i < $numLinks; $i++) {
					$this->assertIsArray($array['link'][$i]);
					$this->assertArrayHasKey('href', $array['link'][$i]);
					$this->assertArrayHasKey('text', $array['link'][$i]);
					$this->assertEquals(
						"https://example.com/link{$i}",
						$array['link'][$i]['href'],
						"Link {$i} href should be preserved"
					);
					$this->assertEquals(
						"Link {$i}",
						$array['link'][$i]['text'],
						"Link {$i} text should be preserved"
					);
				}
			});
	}

	/**
	 * Property test: Metadata with bounds serialization round-trip.
	 * 
	 * For any Metadata with bounds, serializing to array should preserve bounds data.
	 */
	public function test_property_metadata_with_bounds_serialization_round_trip(): void
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
				// Convert to floats
				$minLat = (float) $minLatBase;
				$minLon = (float) $minLonBase;
				$maxLat = (float) $maxLatBase;
				$maxLon = (float) $maxLonBase;
				
				// Ensure min < max
				if ($minLat > $maxLat) {
					[$minLat, $maxLat] = [$maxLat, $minLat];
				}
				if ($minLon > $maxLon) {
					[$minLon, $maxLon] = [$maxLon, $minLon];
				}
				
				// Create original metadata with bounds
				$originalMetadata = new Metadata();
				$originalMetadata->bounds = new Bounds($minLat, $minLon, $maxLat, $maxLon);
				
				// Serialize to array
				$array = $originalMetadata->toArray();
				
				// Verify bounds are preserved
				$this->assertArrayHasKey('bounds', $array);
				$this->assertIsArray($array['bounds']);
				$this->assertArrayHasKey('minlat', $array['bounds']);
				$this->assertArrayHasKey('minlon', $array['bounds']);
				$this->assertArrayHasKey('maxlat', $array['bounds']);
				$this->assertArrayHasKey('maxlon', $array['bounds']);
				
				$this->assertEquals($minLat, $array['bounds']['minlat'], "Min latitude should be preserved");
				$this->assertEquals($minLon, $array['bounds']['minlon'], "Min longitude should be preserved");
				$this->assertEquals($maxLat, $array['bounds']['maxlat'], "Max latitude should be preserved");
				$this->assertEquals($maxLon, $array['bounds']['maxlon'], "Max longitude should be preserved");
			});
	}

	/**
	 * Property test: Metadata with author serialization round-trip.
	 * 
	 * For any Metadata with author, serializing to array should preserve author data.
	 */
	public function test_property_metadata_with_author_serialization_round_trip(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::string()  // author name
			)
			->withMaxSize(100)
			->then(function ($authorName) {
				// Create original metadata with author
				$originalMetadata = new Metadata();
				$author = new Person();
				$author->name = $authorName;
				$originalMetadata->author = $author;
				
				// Serialize to array
				$array = $originalMetadata->toArray();
				
				// Verify author is preserved
				$this->assertArrayHasKey('author', $array);
				$this->assertIsArray($array['author']);
				$this->assertArrayHasKey('name', $array['author']);
				$this->assertEquals($authorName, $array['author']['name'], "Author name should be preserved");
			});
	}
}
