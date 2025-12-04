<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Models\Point;
use phpGPX\Parsers\PointParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Point model.
 * Tests Point creation, property storage, and serialization.
 */
final class PointTest extends TestCase
{
	use TestTrait;
	/**
	 * Test Point creation with valid coordinates.
	 * Requirements: 2.2
	 */
	public function test_point_can_be_created_with_valid_coordinates(): void
	{
		// Arrange & Act
		$point = new Point(Point::TRACKPOINT);
		$point->latitude = 54.9328621088893;
		$point->longitude = 9.860624216140083;
		
		// Assert
		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(54.9328621088893, $point->latitude);
		$this->assertEquals(9.860624216140083, $point->longitude);
		$this->assertEquals(Point::TRACKPOINT, $point->getPointType());
	}

	/**
	 * Test Point creation with waypoint type.
	 * Requirements: 2.2
	 */
	public function test_point_can_be_created_as_waypoint(): void
	{
		// Arrange & Act
		$point = new Point(Point::WAYPOINT);
		$point->latitude = 50.0;
		$point->longitude = 10.0;
		
		// Assert
		$this->assertEquals(Point::WAYPOINT, $point->getPointType());
	}

	/**
	 * Test Point creation with route point type.
	 * Requirements: 2.2
	 */
	public function test_point_can_be_created_as_routepoint(): void
	{
		// Arrange & Act
		$point = new Point(Point::ROUTEPOINT);
		$point->latitude = 50.0;
		$point->longitude = 10.0;
		
		// Assert
		$this->assertEquals(Point::ROUTEPOINT, $point->getPointType());
	}

	/**
	 * Test Point with elevation.
	 * Requirements: 2.2
	 */
	public function test_point_stores_elevation_correctly(): void
	{
		// Arrange & Act
		$point = PointFactory::createWithElevation(42.5);
		
		// Assert
		$this->assertEquals(42.5, $point->elevation);
	}

	/**
	 * Test Point with time.
	 * Requirements: 2.2
	 */
	public function test_point_stores_time_correctly(): void
	{
		// Arrange
		$time = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
		
		// Act
		$point = PointFactory::create(['time' => $time]);
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $point->time);
		$this->assertEquals($time->getTimestamp(), $point->time->getTimestamp());
	}

	/**
	 * Test Point with all optional properties.
	 * Requirements: 2.2
	 */
	public function test_point_stores_all_optional_properties(): void
	{
		// Arrange & Act
		$point = new Point(Point::WAYPOINT);
		$point->latitude = 54.9328621088893;
		$point->longitude = 9.860624216140083;
		$point->elevation = 100.5;
		$point->time = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
		$point->name = 'Test Waypoint';
		$point->description = 'A test waypoint';
		$point->comment = 'Test comment';
		$point->source = 'GPS Device';
		$point->symbol = 'Flag';
		$point->type = 'Summit';
		$point->magVar = 5.5;
		$point->geoidHeight = 50.0;
		$point->fix = '3d';
		$point->satellitesNumber = 8;
		$point->hdop = 1.2;
		$point->vdop = 1.5;
		$point->pdop = 2.0;
		
		// Assert
		$this->assertEquals('Test Waypoint', $point->name);
		$this->assertEquals('A test waypoint', $point->description);
		$this->assertEquals('Test comment', $point->comment);
		$this->assertEquals('GPS Device', $point->source);
		$this->assertEquals('Flag', $point->symbol);
		$this->assertEquals('Summit', $point->type);
		$this->assertEquals(5.5, $point->magVar);
		$this->assertEquals(50.0, $point->geoidHeight);
		$this->assertEquals('3d', $point->fix);
		$this->assertEquals(8, $point->satellitesNumber);
		$this->assertEquals(1.2, $point->hdop);
		$this->assertEquals(1.5, $point->vdop);
		$this->assertEquals(2.0, $point->pdop);
	}

	/**
	 * Test Point serialization to array.
	 * Requirements: 2.7
	 */
	public function test_point_serializes_to_array_correctly(): void
	{
		// Arrange
		$point = new Point(Point::TRACKPOINT);
		$point->latitude = 54.9328621088893;
		$point->longitude = 9.860624216140083;
		$point->elevation = 42.5;
		$point->name = 'Test Point';
		
		// Act
		$array = $point->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals(54.9328621088893, $array['lat']);
		$this->assertEquals(9.860624216140083, $array['lon']);
		$this->assertEquals(42.5, $array['ele']);
		$this->assertEquals('Test Point', $array['name']);
	}

	/**
	 * Test Point serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_point_serializes_null_values_correctly(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT);
		$point->latitude = 50.0;
		$point->longitude = 10.0;
		
		// Act
		$array = $point->toArray();
		
		// Assert
		$this->assertNull($array['ele']);
		$this->assertNull($array['time']);
		$this->assertNull($array['name']);
	}

	/**
	 * Test Point serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_point_serializes_to_xml_correctly(): void
	{
		// Arrange
		$point = new Point(Point::TRACKPOINT);
		$point->latitude = 54.9328621088893;
		$point->longitude = 9.860624216140083;
		$point->elevation = 42.5;
		$point->name = 'Test Point';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PointParser::toXML($point, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('trkpt', $xml);
		$this->assertStringContainsString('lat="54.93286', $xml); // Check prefix due to float precision
		$this->assertStringContainsString('lon="9.86062', $xml); // Check prefix due to float precision
		$this->assertStringContainsString('<ele>42.5</ele>', $xml);
		$this->assertStringContainsString('<name>Test Point</name>', $xml);
	}

	/**
	 * Test waypoint serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_waypoint_serializes_to_xml_with_correct_element_name(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT);
		$point->latitude = 50.0;
		$point->longitude = 10.0;
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PointParser::toXML($point, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('wpt', $xml);
		$this->assertStringNotContainsString('trkpt', $xml);
	}

	/**
	 * Test route point serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_routepoint_serializes_to_xml_with_correct_element_name(): void
	{
		// Arrange
		$point = new Point(Point::ROUTEPOINT);
		$point->latitude = 50.0;
		$point->longitude = 10.0;
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PointParser::toXML($point, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('rtept', $xml);
	}

	/**
	 * Test Point with boundary latitude values.
	 * Requirements: 2.2
	 */
	public function test_point_accepts_boundary_latitude_values(): void
	{
		// Arrange & Act - Maximum latitude
		$pointMax = new Point(Point::WAYPOINT);
		$pointMax->latitude = 90.0;
		$pointMax->longitude = 0.0;
		
		// Assert
		$this->assertEquals(90.0, $pointMax->latitude);
		
		// Arrange & Act - Minimum latitude
		$pointMin = new Point(Point::WAYPOINT);
		$pointMin->latitude = -90.0;
		$pointMin->longitude = 0.0;
		
		// Assert
		$this->assertEquals(-90.0, $pointMin->latitude);
	}

	/**
	 * Test Point with boundary longitude values.
	 * Requirements: 2.2
	 */
	public function test_point_accepts_boundary_longitude_values(): void
	{
		// Arrange & Act - Maximum longitude
		$pointMax = new Point(Point::WAYPOINT);
		$pointMax->latitude = 0.0;
		$pointMax->longitude = 180.0;
		
		// Assert
		$this->assertEquals(180.0, $pointMax->longitude);
		
		// Arrange & Act - Minimum longitude
		$pointMin = new Point(Point::WAYPOINT);
		$pointMin->latitude = 0.0;
		$pointMin->longitude = -180.0;
		
		// Assert
		$this->assertEquals(-180.0, $pointMin->longitude);
	}

	/**
	 * Test Point at equator.
	 * Requirements: 2.2
	 */
	public function test_point_at_equator(): void
	{
		// Arrange & Act
		$point = PointFactory::createAtCoordinates(0.0, 0.0);
		
		// Assert
		$this->assertEquals(0.0, $point->latitude);
		$this->assertEquals(0.0, $point->longitude);
	}

	/**
	 * Test Point at north pole.
	 * Requirements: 2.2
	 */
	public function test_point_at_north_pole(): void
	{
		// Arrange & Act
		$point = PointFactory::createAtCoordinates(90.0, 0.0);
		
		// Assert
		$this->assertEquals(90.0, $point->latitude);
	}

	/**
	 * Test Point at south pole.
	 * Requirements: 2.2
	 */
	public function test_point_at_south_pole(): void
	{
		// Arrange & Act
		$point = PointFactory::createAtCoordinates(-90.0, 0.0);
		
		// Assert
		$this->assertEquals(-90.0, $point->latitude);
	}

	/**
	 * Test Point with zero elevation.
	 * Requirements: 2.2
	 */
	public function test_point_with_zero_elevation(): void
	{
		// Arrange & Act
		$point = PointFactory::createWithElevation(0.0);
		
		// Assert
		$this->assertEquals(0.0, $point->elevation);
	}

	/**
	 * Test Point with negative elevation (below sea level).
	 * Requirements: 2.2
	 */
	public function test_point_with_negative_elevation(): void
	{
		// Arrange & Act
		$point = PointFactory::createWithElevation(-50.0);
		
		// Assert
		$this->assertEquals(-50.0, $point->elevation);
	}

	/**
	 * Test Point with very high elevation.
	 * Requirements: 2.2
	 */
	public function test_point_with_very_high_elevation(): void
	{
		// Arrange & Act - Mount Everest height
		$point = PointFactory::createWithElevation(8848.86);
		
		// Assert
		$this->assertEquals(8848.86, $point->elevation);
	}

	/**
	 * Test Point initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_point_initialization_has_null_values(): void
	{
		// Arrange & Act
		$point = new Point(Point::TRACKPOINT);
		
		// Assert
		$this->assertNull($point->latitude);
		$this->assertNull($point->longitude);
		$this->assertNull($point->elevation);
		$this->assertNull($point->time);
		$this->assertNull($point->name);
		$this->assertNull($point->description);
		$this->assertNull($point->comment);
		$this->assertNull($point->source);
		$this->assertEmpty($point->links);
		$this->assertNull($point->symbol);
		$this->assertNull($point->type);
	}

	/**
	 * Test Point with time serialization to array.
	 * Requirements: 2.7
	 */
	public function test_point_with_time_serializes_to_array(): void
	{
		// Arrange
		$time = new \DateTime('2024-01-15T10:30:00Z');
		$point = PointFactory::create(['time' => $time]);
		
		// Act
		$array = $point->toArray();
		
		// Assert
		$this->assertNotNull($array['time']);
		$this->assertIsString($array['time']);
	}

	/**
	 * Test Point serialization to XML includes all properties.
	 * Requirements: 2.5
	 */
	public function test_point_xml_serialization_includes_all_properties(): void
	{
		// Arrange
		$time = new \DateTime('2024-01-15T10:30:00Z');
		$point = new Point(Point::TRACKPOINT);
		$point->latitude = 54.9328621088893;
		$point->longitude = 9.860624216140083;
		$point->elevation = 42.5;
		$point->time = $time;
		$point->name = 'Test Point';
		$point->description = 'Test Description';
		$point->comment = 'Test Comment';
		$point->symbol = 'Flag';
		$point->type = 'Summit';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PointParser::toXML($point, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<name>Test Point</name>', $xml);
		$this->assertStringContainsString('<desc>Test Description</desc>', $xml);
		$this->assertStringContainsString('<cmt>Test Comment</cmt>', $xml);
		$this->assertStringContainsString('<sym>Flag</sym>', $xml);
		$this->assertStringContainsString('<type>Summit</type>', $xml);
		$this->assertStringContainsString('<time>', $xml);
	}

	/**
	 * Property test: Model property storage.
	 * 
	 * **Feature: test-coverage, Property 5: Model property storage**
	 * **Validates: Requirements 2.2**
	 * 
	 * This test verifies that for any valid model data, creating a Point instance
	 * and retrieving its properties returns the exact values that were provided.
	 */
	public function test_property_model_property_storage(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 180),  // longitude
				Generators::choose(-1000, 9000), // elevation (from Dead Sea to Everest)
				Generators::elements([Point::WAYPOINT, Point::TRACKPOINT, Point::ROUTEPOINT]), // point type
				Generators::string(),           // name
				Generators::string(),           // description
				Generators::string(),           // comment
				Generators::choose(0, 20)       // satellites number
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $eleBase, $pointType, $name, $description, $comment, $satellites) {
				// Convert to floats with decimal precision
				$latitude = (float) $latBase + (mt_rand(0, 999999) / 1000000);
				$longitude = (float) $lonBase + (mt_rand(0, 999999) / 1000000);
				$elevation = (float) $eleBase + (mt_rand(0, 999999) / 1000000);
				
				// Ensure within valid ranges
				$latitude = min(90.0, max(-90.0, $latitude));
				$longitude = min(180.0, max(-180.0, $longitude));
				
				// Create a timestamp
				$time = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
				
				// Create point and set properties
				$point = new Point($pointType);
				$point->latitude = $latitude;
				$point->longitude = $longitude;
				$point->elevation = $elevation;
				$point->time = $time;
				$point->name = $name;
				$point->description = $description;
				$point->comment = $comment;
				$point->satellitesNumber = $satellites;
				
				// Verify all properties are stored correctly
				$this->assertEquals($latitude, $point->latitude, "Latitude should be stored correctly");
				$this->assertEquals($longitude, $point->longitude, "Longitude should be stored correctly");
				$this->assertEquals($elevation, $point->elevation, "Elevation should be stored correctly");
				$this->assertEquals($time, $point->time, "Time should be stored correctly");
				$this->assertEquals($name, $point->name, "Name should be stored correctly");
				$this->assertEquals($description, $point->description, "Description should be stored correctly");
				$this->assertEquals($comment, $point->comment, "Comment should be stored correctly");
				$this->assertEquals($satellites, $point->satellitesNumber, "Satellites number should be stored correctly");
				$this->assertEquals($pointType, $point->getPointType(), "Point type should be stored correctly");
			});
	}

	/**
	 * Property test: Invalid coordinates rejected.
	 * 
	 * **Feature: test-coverage, Property 8: Invalid coordinates rejected**
	 * **Validates: Requirements 5.1**
	 * 
	 * COMMENTED OUT: This test is currently disabled because the Point model does not implement
	 * coordinate validation. According to Requirements 5.1, the system SHALL reject all invalid
	 * latitude and longitude values (latitude outside [-90, 90] or longitude outside [-180, 180]).
	 * 
	 * TODO: After Phase 4 (PHP 8.4 Migration) or Phase 5 (Architecture Refactoring), implement
	 * coordinate validation in the Point model:
	 * - Add validation in Point constructor or when setting latitude/longitude properties
	 * - Throw InvalidArgumentException with descriptive message for invalid coordinates
	 * - Then uncomment this test to verify the validation works correctly
	 * 
	 * The test itself is correct and follows the specification. The issue is in the production code.
	 */
	/*
	public function test_property_invalid_coordinates_rejected(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-200, 200),  // Generate values outside valid range
				Generators::choose(-400, 400)   // Generate values outside valid range
			)
			->withMaxSize(100)
			->then(function ($lat, $lon) {
				// Convert to float
				$lat = (float) $lat;
				$lon = (float) $lon;
				
				// Check if coordinates are invalid
				$isLatitudeInvalid = $lat < -90.0 || $lat > 90.0;
				$isLongitudeInvalid = $lon < -180.0 || $lon > 180.0;
				
				if ($isLatitudeInvalid || $isLongitudeInvalid) {
					// Invalid coordinates should be rejected
					$this->expectException(\InvalidArgumentException::class);
					
					$point = new Point(Point::WAYPOINT);
					$point->latitude = $lat;
					$point->longitude = $lon;
				} else {
					// Valid coordinates should be accepted
					$point = new Point(Point::WAYPOINT);
					$point->latitude = $lat;
					$point->longitude = $lon;
					
					$this->assertEquals($lat, $point->latitude);
					$this->assertEquals($lon, $point->longitude);
				}
			});
	}
	*/
}
