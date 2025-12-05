<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use DateTime;
use DateTimeZone;
use DOMDocument;
use Eris\Generators;
use Eris\TestTrait;
use InvalidArgumentException;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use phpGPX\Parsers\PointParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;
use ReflectionClass;

/**
 * Unit tests for Point model.
 * Tests Point creation, property storage, and serialization.
 */
final class PointTest extends TestCase
{
	use TestTrait;

	/**
	 * Test Point creation with valid coordinates.
	 * Requirements: 1.1, 1.2
	 */
	public function test_point_can_be_created_with_valid_coordinates(): void
	{
		// Arrange & Act
		$point = new Point(Point::TRACKPOINT, 54.9328621088893, 9.860624216140083);

		// Assert
		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(54.9328621088893, $point->latitude);
		$this->assertEquals(9.860624216140083, $point->longitude);
		$this->assertSame(PointType::TRACKPOINT, $point->getPointType());
	}

	/**
	 * Test Point construction with invalid latitude (too low).
	 * Requirements: 1.4, 9.1, 9.4
	 */
	public function test_point_rejects_latitude_below_minimum(): void
	{
		// Arrange & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('-95.5');

		// Act
		new Point(Point::WAYPOINT, -95.5, 0.0);
	}

	/**
	 * Test Point construction with invalid latitude (too high).
	 * Requirements: 1.4, 9.1, 9.4
	 */
	public function test_point_rejects_latitude_above_maximum(): void
	{
		// Arrange & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('95.5');

		// Act
		new Point(Point::WAYPOINT, 95.5, 0.0);
	}

	/**
	 * Test Point construction with invalid longitude (too low).
	 * Requirements: 1.5, 9.2, 9.4
	 */
	public function test_point_rejects_longitude_below_minimum(): void
	{
		// Arrange & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('-185.5');

		// Act
		new Point(Point::WAYPOINT, 0.0, -185.5);
	}

	/**
	 * Test Point construction with invalid longitude (at or above maximum).
	 * Requirements: 1.5, 9.2, 9.4
	 */
	public function test_point_rejects_longitude_at_or_above_maximum(): void
	{
		// Arrange & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('180.0');

		// Act
		new Point(Point::WAYPOINT, 0.0, 180.0);
	}

	/**
	 * Test Point accepts boundary latitude values.
	 * Requirements: 1.4
	 */
	public function test_point_accepts_boundary_latitude_values(): void
	{
		// Act - Maximum latitude
		$pointMax = new Point(Point::WAYPOINT, 90.0, 0.0);

		// Assert
		$this->assertEquals(90.0, $pointMax->latitude);

		// Act - Minimum latitude
		$pointMin = new Point(Point::WAYPOINT, -90.0, 0.0);

		// Assert
		$this->assertEquals(-90.0, $pointMin->latitude);
	}

	/**
	 * Test Point accepts boundary longitude values.
	 * Requirements: 1.5
	 */
	public function test_point_accepts_boundary_longitude_values(): void
	{
		// Act - Just below maximum longitude (179.999...)
		$pointMax = new Point(Point::WAYPOINT, 0.0, 179.999999);

		// Assert
		$this->assertEquals(179.999999, $pointMax->longitude);

		// Act - Minimum longitude
		$pointMin = new Point(Point::WAYPOINT, 0.0, -180.0);

		// Assert
		$this->assertEquals(-180.0, $pointMin->longitude);
	}

	/**
	 * Test setMagVar with valid value.
	 * Requirements: 8.1
	 */
	public function test_set_mag_var_accepts_valid_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act
		$point->setMagVar(45.5);

		// Assert
		$this->assertEquals(45.5, $point->magVar);
	}

	/**
	 * Test setMagVar with boundary values.
	 * Requirements: 8.1
	 */
	public function test_set_mag_var_accepts_boundary_values(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act & Assert - Minimum (0.0)
		$point->setMagVar(0.0);
		$this->assertEquals(0.0, $point->magVar);

		// Act & Assert - Just below maximum (359.999...)
		$point->setMagVar(359.999999);
		$this->assertEquals(359.999999, $point->magVar);
	}

	/**
	 * Test setMagVar rejects negative value.
	 * Requirements: 8.1, 9.4
	 */
	public function test_set_mag_var_rejects_negative_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0.0');
		$this->expectExceptionMessage('360.0');
		$this->expectExceptionMessage('-5.0');

		// Act
		$point->setMagVar(-5.0);
	}

	/**
	 * Test setMagVar rejects value at or above 360.
	 * Requirements: 8.1, 9.4
	 */
	public function test_set_mag_var_rejects_value_at_or_above_360(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0.0');
		$this->expectExceptionMessage('360.0');
		$this->expectExceptionMessage('360.0');

		// Act
		$point->setMagVar(360.0);
	}

	/**
	 * Test setDgpsId with valid value.
	 * Requirements: 7.1
	 */
	public function test_set_dgps_id_accepts_valid_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act
		$point->setDgpsId(512);

		// Assert
		$this->assertEquals(512, $point->dgpsid);
	}

	/**
	 * Test setDgpsId with boundary values.
	 * Requirements: 7.1
	 */
	public function test_set_dgps_id_accepts_boundary_values(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act & Assert - Minimum (0)
		$point->setDgpsId(0);
		$this->assertEquals(0, $point->dgpsid);

		// Act & Assert - Maximum (1023)
		$point->setDgpsId(1023);
		$this->assertEquals(1023, $point->dgpsid);
	}

	/**
	 * Test setDgpsId rejects negative value.
	 * Requirements: 7.1, 9.4
	 */
	public function test_set_dgps_id_rejects_negative_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0');
		$this->expectExceptionMessage('1023');
		$this->expectExceptionMessage('-5');

		// Act
		$point->setDgpsId(-5);
	}

	/**
	 * Test setDgpsId rejects value above 1023.
	 * Requirements: 7.1, 9.4
	 */
	public function test_set_dgps_id_rejects_value_above_maximum(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('0');
		$this->expectExceptionMessage('1023');
		$this->expectExceptionMessage('1024');

		// Act
		$point->setDgpsId(1024);
	}

	/**
	 * Test setSat with valid value.
	 * Requirements: 7A.1
	 */
	public function test_set_sat_accepts_valid_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act
		$point->setSat(8);

		// Assert
		$this->assertEquals(8, $point->satellitesNumber);
	}

	/**
	 * Test setSat with boundary value (zero).
	 * Requirements: 7A.1
	 */
	public function test_set_sat_accepts_zero(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act
		$point->setSat(0);

		// Assert
		$this->assertEquals(0, $point->satellitesNumber);
	}

	/**
	 * Test setSat rejects negative value.
	 * Requirements: 7A.1, 9.4
	 */
	public function test_set_sat_rejects_negative_value(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Satellite count');
		$this->expectExceptionMessage('non-negative');
		$this->expectExceptionMessage('-5');

		// Act
		$point->setSat(-5);
	}

	/**
	 * Test Point creation with waypoint type.
	 * Requirements: 1.1, 1.2
	 */
	public function test_point_can_be_created_as_waypoint(): void
	{
		// Arrange & Act
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Assert
		$this->assertSame(PointType::WAYPOINT, $point->getPointType());
	}

	/**
	 * Test Point creation with route point type.
	 * Requirements: 1.1, 1.2
	 */
	public function test_point_can_be_created_as_routepoint(): void
	{
		// Arrange & Act
		$point = new Point(Point::ROUTEPOINT, 50.0, 10.0);

		// Assert
		$this->assertSame(PointType::ROUTEPOINT, $point->getPointType());
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
		$time = new DateTime('2024-01-15 10:30:00', new DateTimeZone('UTC'));

		// Act
		$point = PointFactory::create(['time' => $time]);

		// Assert
		$this->assertInstanceOf(DateTime::class, $point->time);
		$this->assertEquals($time->getTimestamp(), $point->time->getTimestamp());
	}

	/**
	 * Test Point with all optional properties.
	 * Requirements: 1.1, 1.2
	 */
	public function test_point_stores_all_optional_properties(): void
	{
		// Arrange & Act
		$point = new Point(Point::WAYPOINT, 54.9328621088893, 9.860624216140083);
		$point->elevation = 100.5;
		$point->time = new DateTime('2024-01-15 10:30:00', new DateTimeZone('UTC'));
		$point->name = 'Test Waypoint';
		$point->description = 'A test waypoint';
		$point->comment = 'Test comment';
		$point->source = 'GPS Device';
		$point->symbol = 'Flag';
		$point->type = 'Summit';
		$point->setMagVar(5.5);
		$point->geoidHeight = 50.0;
		$point->fix = '3d';
		$point->setSat(8);
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
	 * Requirements: 1.3
	 */
	public function test_point_serializes_to_array_correctly(): void
	{
		// Arrange
		$point = new Point(Point::TRACKPOINT, 54.9328621088893, 9.860624216140083);
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
	 * Requirements: 1.3
	 */
	public function test_point_serializes_null_values_correctly(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		// Act
		$array = $point->toArray();

		// Assert
		$this->assertNull($array['ele']);
		$this->assertNull($array['time']);
		$this->assertNull($array['name']);
	}

	/**
	 * Test Point serialization to XML.
	 * Requirements: 1.3
	 */
	public function test_point_serializes_to_xml_correctly(): void
	{
		// Arrange
		$point = new Point(Point::TRACKPOINT, 54.9328621088893, 9.860624216140083);
		$point->elevation = 42.5;
		$point->name = 'Test Point';

		$document = new DOMDocument('1.0', 'UTF-8');

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
	 * Requirements: 1.3
	 */
	public function test_waypoint_serializes_to_xml_with_correct_element_name(): void
	{
		// Arrange
		$point = new Point(Point::WAYPOINT, 50.0, 10.0);

		$document = new DOMDocument('1.0', 'UTF-8');

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
	 * Requirements: 1.3
	 */
	public function test_routepoint_serializes_to_xml_with_correct_element_name(): void
	{
		// Arrange
		$point = new Point(Point::ROUTEPOINT, 50.0, 10.0);

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = PointParser::toXML($point, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('rtept', $xml);
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
	 * Test Point initialization has null values for optional properties.
	 * Requirements: 1.1, 1.2
	 */
	public function test_point_initialization_has_null_values(): void
	{
		// Arrange & Act
		$point = new Point(Point::TRACKPOINT, 50.0, 10.0);

		// Assert - latitude and longitude are required, all other properties should be null
		$this->assertEquals(50.0, $point->latitude);
		$this->assertEquals(10.0, $point->longitude);
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
		$time = new DateTime('2024-01-15T10:30:00Z');
		$point = PointFactory::create(['time' => $time]);

		// Act
		$array = $point->toArray();

		// Assert
		$this->assertNotNull($array['time']);
		$this->assertIsString($array['time']);
	}

	/**
	 * Test Point serialization to XML includes all properties.
	 * Requirements: 1.3
	 */
	public function test_point_xml_serialization_includes_all_properties(): void
	{
		// Arrange
		$time = new DateTime('2024-01-15T10:30:00Z');
		$point = new Point(Point::TRACKPOINT, 54.9328621088893, 9.860624216140083);
		$point->elevation = 42.5;
		$point->time = $time;
		$point->name = 'Test Point';
		$point->description = 'Test Description';
		$point->comment = 'Test Comment';
		$point->symbol = 'Flag';
		$point->type = 'Summit';

		$document = new DOMDocument('1.0', 'UTF-8');

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
	 * **Validates: Requirements 1.1, 1.2**
	 *
	 * This test verifies that for any valid model data, creating a Point instance
	 * and retrieving its properties returns the exact values that were provided.
	 */
	public function test_property_model_property_storage(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 89),    // latitude (avoid 90 to ensure we can add decimals)
				Generators::choose(-180, 179),  // longitude (avoid 180 which is exclusive)
				Generators::choose(-1000, 9000), // elevation (from Dead Sea to Everest)
				Generators::elements([Point::WAYPOINT, Point::TRACKPOINT, Point::ROUTEPOINT]), // point type
				Generators::string(),           // name
				Generators::string(),           // description
				Generators::string(),           // comment
				Generators::choose(0, 20),       // satellites number
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase, $eleBase, $pointType, $name, $description, $comment, $satellites): void {
				// Convert to floats with decimal precision
				$latitude = (float) $latBase + (mt_rand(0, 999999) / 1000000);
				$longitude = (float) $lonBase + (mt_rand(0, 999999) / 1000000);
				$elevation = (float) $eleBase + (mt_rand(0, 999999) / 1000000);

				// Ensure within valid ranges
				$latitude = min(90.0, max(-90.0, $latitude));
				$longitude = min(179.999999, max(-180.0, $longitude));

				// Create a timestamp
				$time = new DateTime('2024-01-15 10:30:00', new DateTimeZone('UTC'));

				// Create point with required coordinates
				$point = new Point($pointType, $latitude, $longitude);
				$point->elevation = $elevation;
				$point->time = $time;
				$point->name = $name;
				$point->description = $description;
				$point->comment = $comment;
				$point->setSat($satellites);

				// Verify all properties are stored correctly
				$this->assertEquals($latitude, $point->latitude, "Latitude should be stored correctly");
				$this->assertEquals($longitude, $point->longitude, "Longitude should be stored correctly");
				$this->assertEquals($elevation, $point->elevation, "Elevation should be stored correctly");
				$this->assertEquals($time, $point->time, "Time should be stored correctly");
				$this->assertEquals($name, $point->name, "Name should be stored correctly");
				$this->assertEquals($description, $point->description, "Description should be stored correctly");
				$this->assertEquals($comment, $point->comment, "Comment should be stored correctly");
				$this->assertEquals($satellites, $point->satellitesNumber, "Satellites number should be stored correctly");
				// Point type is converted to enum, so compare the enum value
				$this->assertEquals($pointType, $point->getPointType()->value, "Point type should be stored correctly");
			});
	}

	/**
	 * Property test: Latitude range validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 3: Latitude range validation**
	 * **Validates: Requirements 1.4, 2.4, 2.5**
	 *
	 * This test verifies that for any latitude value outside the range [-90.0, 90.0],
	 * the Point Model SHALL throw an InvalidArgumentException.
	 */
	public function test_property_latitude_range_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-200, 200),  // Generate values that may be outside valid range
			)
			->withMaxSize(100)
			->then(function ($latBase): void {
				// Convert to float with decimal precision
				$lat = (float) $latBase + (mt_rand(0, 999999) / 1000000);

				// Check if latitude is invalid
				$isInvalid = $lat < -90.0 || $lat > 90.0;

				if ($isInvalid) {
					// Invalid latitude should be rejected
					try {
						new Point(Point::WAYPOINT, $lat, 0.0);
						$this->fail('Expected InvalidArgumentException for latitude ' . $lat);
					} catch (InvalidArgumentException $e) {
						// Verify error message contains range and invalid value
						$this->assertStringContainsString('-90.0', $e->getMessage());
						$this->assertStringContainsString('90.0', $e->getMessage());
						$this->assertStringContainsString((string) $lat, $e->getMessage());
					}
				} else {
					// Valid latitude should be accepted
					$point = new Point(Point::WAYPOINT, $lat, 0.0);
					$this->assertEquals($lat, $point->latitude);
				}
			});
	}

	/**
	 * Property test: Longitude range validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 4: Longitude range validation**
	 * **Validates: Requirements 1.5, 2.4, 2.5**
	 *
	 * This test verifies that for any longitude value outside the range [-180.0, 180.0),
	 * the Point Model SHALL throw an InvalidArgumentException.
	 */
	public function test_property_longitude_range_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-400, 400),  // Generate values that may be outside valid range
			)
			->withMaxSize(100)
			->then(function ($lonBase): void {
				// Convert to float with decimal precision
				$lon = (float) $lonBase + (mt_rand(0, 999999) / 1000000);

				// Check if longitude is invalid (note: 180.0 is exclusive)
				$isInvalid = $lon < -180.0 || $lon >= 180.0;

				if ($isInvalid) {
					// Invalid longitude should be rejected
					try {
						new Point(Point::WAYPOINT, 0.0, $lon);
						$this->fail('Expected InvalidArgumentException for longitude ' . $lon);
					} catch (InvalidArgumentException $e) {
						// Verify error message contains range and invalid value
						$this->assertStringContainsString('-180.0', $e->getMessage());
						$this->assertStringContainsString('180.0', $e->getMessage());
						$this->assertStringContainsString((string) $lon, $e->getMessage());
					}
				} else {
					// Valid longitude should be accepted
					$point = new Point(Point::WAYPOINT, 0.0, $lon);
					$this->assertEquals($lon, $point->longitude);
				}
			});
	}

	/**
	 * Property test: DGPS station ID validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 21: DGPS station ID validation**
	 * **Validates: Requirements 7.1, 7A.1, 8.1**
	 *
	 * This test verifies that for any DGPS station ID outside the range [0, 1023],
	 * the Point Model SHALL throw an InvalidArgumentException.
	 */
	public function test_property_dgps_station_id_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-100, 1200),  // Generate values that may be outside valid range
			)
			->withMaxSize(100)
			->then(function ($dgpsId): void {
				$point = new Point(Point::WAYPOINT, 50.0, 10.0);

				// Check if DGPS ID is invalid
				$isInvalid = $dgpsId < 0 || $dgpsId > 1023;

				if ($isInvalid) {
					// Invalid DGPS ID should be rejected
					try {
						$point->setDgpsId($dgpsId);
						$this->fail('Expected InvalidArgumentException for DGPS ID ' . $dgpsId);
					} catch (InvalidArgumentException $e) {
						// Verify error message contains range and invalid value
						$this->assertStringContainsString('0', $e->getMessage());
						$this->assertStringContainsString('1023', $e->getMessage());
						$this->assertStringContainsString((string) $dgpsId, $e->getMessage());
					}
				} else {
					// Valid DGPS ID should be accepted
					$point->setDgpsId($dgpsId);
					$this->assertEquals($dgpsId, $point->dgpsid);
				}
			});
	}

	/**
	 * Property test: Satellite count validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 22: Satellite count validation**
	 * **Validates: Requirements 7.1, 7A.1, 8.1**
	 *
	 * This test verifies that for any negative satellite count,
	 * the Point Model SHALL throw an InvalidArgumentException.
	 */
	public function test_property_satellite_count_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-100, 50),  // Generate values that may be negative
			)
			->withMaxSize(100)
			->then(function ($sat): void {
				$point = new Point(Point::WAYPOINT, 50.0, 10.0);

				// Check if satellite count is invalid
				$isInvalid = $sat < 0;

				if ($isInvalid) {
					// Negative satellite count should be rejected
					try {
						$point->setSat($sat);
						$this->fail('Expected InvalidArgumentException for satellite count ' . $sat);
					} catch (InvalidArgumentException $e) {
						// Verify error message contains "non-negative" and invalid value
						$this->assertStringContainsString('non-negative', $e->getMessage());
						$this->assertStringContainsString((string) $sat, $e->getMessage());
					}
				} else {
					// Valid satellite count should be accepted
					$point->setSat($sat);
					$this->assertEquals($sat, $point->satellitesNumber);
				}
			});
	}

	/**
	 * Property test: Magnetic variation validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 23: Magnetic variation validation**
	 * **Validates: Requirements 7.1, 7A.1, 8.1**
	 *
	 * This test verifies that for any magnetic variation value outside the range [0.0, 360.0),
	 * the Point Model SHALL throw an InvalidArgumentException.
	 */
	public function test_property_magnetic_variation_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-100, 400),  // Generate values that may be outside valid range
			)
			->withMaxSize(100)
			->then(function ($magVarBase): void {
				// Convert to float with decimal precision
				$magVar = (float) $magVarBase + (mt_rand(0, 999999) / 1000000);

				$point = new Point(Point::WAYPOINT, 50.0, 10.0);

				// Check if magnetic variation is invalid (note: 360.0 is exclusive)
				$isInvalid = $magVar < 0.0 || $magVar >= 360.0;

				if ($isInvalid) {
					// Invalid magnetic variation should be rejected
					try {
						$point->setMagVar($magVar);
						$this->fail('Expected InvalidArgumentException for magnetic variation ' . $magVar);
					} catch (InvalidArgumentException $e) {
						// Verify error message contains range and invalid value
						$this->assertStringContainsString('0.0', $e->getMessage());
						$this->assertStringContainsString('360.0', $e->getMessage());
						$this->assertStringContainsString((string) $magVar, $e->getMessage());
					}
				} else {
					// Valid magnetic variation should be accepted
					$point->setMagVar($magVar);
					$this->assertEquals($magVar, $point->magVar);
				}
			});
	}

	/**
	 * Property test: Latitude validation error messages.
	 *
	 * **Feature: gpx-schema-compliance, Property 24: Latitude validation error messages**
	 * **Validates: Requirements 9.1, 9.2, 9.4**
	 *
	 * This test verifies that for any latitude validation failure, the exception message
	 * SHALL include the text "-90.0" and "90.0" indicating the valid range.
	 */
	public function test_property_latitude_validation_error_messages(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-200, -91),  // Invalid: too small
				Generators::choose(91, 200),      // Invalid: too large
			)
			->withMaxSize(100)
			->then(function ($invalidLow, $invalidHigh): void {
				// Test invalid low latitude
				try {
					new Point(Point::WAYPOINT, (float)$invalidLow, 0.0);
					$this->fail('Expected InvalidArgumentException for latitude ' . $invalidLow);
				} catch (InvalidArgumentException $e) {
					$this->assertStringContainsString('-90.0', $e->getMessage());
					$this->assertStringContainsString('90.0', $e->getMessage());
					$this->assertStringContainsString((string)(float)$invalidLow, $e->getMessage());
				}

				// Test invalid high latitude
				try {
					new Point(Point::WAYPOINT, (float)$invalidHigh, 0.0);
					$this->fail('Expected InvalidArgumentException for latitude ' . $invalidHigh);
				} catch (InvalidArgumentException $e) {
					$this->assertStringContainsString('-90.0', $e->getMessage());
					$this->assertStringContainsString('90.0', $e->getMessage());
					$this->assertStringContainsString((string)(float)$invalidHigh, $e->getMessage());
				}
			});
	}

	/**
	 * Property test: Longitude validation error messages.
	 *
	 * **Feature: gpx-schema-compliance, Property 25: Longitude validation error messages**
	 * **Validates: Requirements 9.1, 9.2, 9.4**
	 *
	 * This test verifies that for any longitude validation failure, the exception message
	 * SHALL include the text "-180.0" and "180.0" indicating the valid range.
	 */
	public function test_property_longitude_validation_error_messages(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-400, -181),  // Invalid: too small
				Generators::choose(180, 400),      // Invalid: too large (180.0 is exclusive)
			)
			->withMaxSize(100)
			->then(function ($invalidLow, $invalidHigh): void {
				// Test invalid low longitude
				try {
					new Point(Point::WAYPOINT, 0.0, (float)$invalidLow);
					$this->fail('Expected InvalidArgumentException for longitude ' . $invalidLow);
				} catch (InvalidArgumentException $e) {
					$this->assertStringContainsString('-180.0', $e->getMessage());
					$this->assertStringContainsString('180.0', $e->getMessage());
					$this->assertStringContainsString((string)(float)$invalidLow, $e->getMessage());
				}

				// Test invalid high longitude
				try {
					new Point(Point::WAYPOINT, 0.0, (float)$invalidHigh);
					$this->fail('Expected InvalidArgumentException for longitude ' . $invalidHigh);
				} catch (InvalidArgumentException $e) {
					$this->assertStringContainsString('-180.0', $e->getMessage());
					$this->assertStringContainsString('180.0', $e->getMessage());
					$this->assertStringContainsString((string)(float)$invalidHigh, $e->getMessage());
				}
			});
	}

	/**
	 * Property test: Validation error messages include invalid value.
	 *
	 * **Feature: gpx-schema-compliance, Property 27: Validation error messages include invalid value**
	 * **Validates: Requirements 9.1, 9.2, 9.4**
	 *
	 * This test verifies that for any validation failure, the exception message
	 * SHALL include the invalid value that was provided.
	 */
	public function test_property_validation_error_messages_include_invalid_value(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-200, 200),   // Latitude that may be invalid
				Generators::choose(-400, 400),   // Longitude that may be invalid
				Generators::choose(-100, 1200),  // DGPS ID that may be invalid
				Generators::choose(-100, 50),     // Satellite count that may be invalid
			)
			->withMaxSize(100)
			->then(function ($lat, $lon, $dgpsId, $sat): void {
				// Test latitude validation error message
				if ($lat < -90 || $lat > 90) {
					try {
						new Point(Point::WAYPOINT, (float)$lat, 0.0);
						$this->fail('Expected InvalidArgumentException for latitude ' . $lat);
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString((string)(float)$lat, $e->getMessage());
					}
				}

				// Test longitude validation error message
				if ($lon < -180 || $lon >= 180) {
					try {
						new Point(Point::WAYPOINT, 0.0, (float)$lon);
						$this->fail('Expected InvalidArgumentException for longitude ' . $lon);
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString((string)(float)$lon, $e->getMessage());
					}
				}

				// Test DGPS ID validation error message
				if ($dgpsId < 0 || $dgpsId > 1023) {
					$point = new Point(Point::WAYPOINT, 50.0, 10.0);

					try {
						$point->setDgpsId($dgpsId);
						$this->fail('Expected InvalidArgumentException for DGPS ID ' . $dgpsId);
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString((string)$dgpsId, $e->getMessage());
					}
				}

				// Test satellite count validation error message
				if ($sat < 0) {
					$point = new Point(Point::WAYPOINT, 50.0, 10.0);

					try {
						$point->setSat($sat);
						$this->fail('Expected InvalidArgumentException for satellite count ' . $sat);
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString((string)$sat, $e->getMessage());
					}
				}
			});
	}

	/**
	 * Property 1: Point requires latitude and longitude
	 *
	 * For any attempt to create a Point without providing latitude and longitude parameters,
	 * the Type System SHALL throw a TypeError.
	 *
	 * **Feature: gpx-schema-compliance, Property 1: Point requires latitude and longitude**
	 * **Validates: Requirements 1.1, 1.2**
	 *
	 * This test verifies that the PHP type system enforces required parameters.
	 * Since this is enforced by PHP's type system at compile/runtime, we test
	 * various scenarios where parameters are missing.
	 */
	public function test_property_point_requires_latitude_and_longitude(): void
	{
		// Test 1: Missing all coordinate parameters (only pointType provided)
		// This would be: new Point(PointType::WAYPOINT)
		// PHP will throw TypeError: Too few arguments

		// We can't directly test this in a way that compiles, but we can document
		// that the type system enforces this. Instead, we verify the constructor
		// signature requires these parameters by reflection.

		$reflection = new ReflectionClass(Point::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();

		// Verify constructor has at least 3 parameters
		$this->assertGreaterThanOrEqual(3, count($parameters), 'Point constructor must have at least 3 parameters');

		// Verify first parameter is pointType
		$this->assertSame('pointType', $parameters[0]->getName());

		// Verify second parameter is latitude and is required (not optional)
		$this->assertSame('latitude', $parameters[1]->getName());
		$this->assertFalse($parameters[1]->isOptional(), 'Latitude parameter must be required');
		$this->assertTrue($parameters[1]->hasType(), 'Latitude parameter must have a type');
		$this->assertSame('float', $parameters[1]->getType()->getName());

		// Verify third parameter is longitude and is required (not optional)
		$this->assertSame('longitude', $parameters[2]->getName());
		$this->assertFalse($parameters[2]->isOptional(), 'Longitude parameter must be required');
		$this->assertTrue($parameters[2]->hasType(), 'Longitude parameter must have a type');
		$this->assertSame('float', $parameters[2]->getType()->getName());

		// Additional verification: Ensure we can create a Point with all required parameters
		$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
		$this->assertInstanceOf(Point::class, $point);
		$this->assertSame(54.93, $point->latitude);
		$this->assertSame(9.86, $point->longitude);
	}
}
