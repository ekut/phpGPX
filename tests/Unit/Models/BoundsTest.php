<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use DOMDocument;
use Eris\Generators;
use Eris\TestTrait;
use InvalidArgumentException;
use phpGPX\Models\Bounds;
use phpGPX\Parsers\BoundsParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Bounds model.
 * Tests Bounds creation, validation, and serialization.
 */
final class BoundsTest extends TestCase
{
	use TestTrait;

	/**
	 * Test Bounds creation with valid coordinates.
	 * Requirements: 2.2
	 */
	public function test_bounds_can_be_created_with_valid_coordinates(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: 18.814543,
			maxLatitude: 49.090543,
			maxLongitude: 18.886939,
		);

		// Assert
		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(49.072489, $bounds->minLatitude);
		$this->assertEquals(18.814543, $bounds->minLongitude);
		$this->assertEquals(49.090543, $bounds->maxLatitude);
		$this->assertEquals(18.886939, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds rejects invalid minimum latitude (too low).
	 * Requirements: 2.4, 9.4
	 */
	public function test_bounds_rejects_invalid_min_latitude_too_low(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('-95.5');

		new Bounds(
			minLatitude: -95.5,
			minLongitude: 0.0,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid minimum latitude (too high).
	 * Requirements: 2.4, 9.4
	 */
	public function test_bounds_rejects_invalid_min_latitude_too_high(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('95.5');

		new Bounds(
			minLatitude: 95.5,
			minLongitude: 0.0,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid maximum latitude (too low).
	 * Requirements: 2.4, 9.4
	 */
	public function test_bounds_rejects_invalid_max_latitude_too_low(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('-95.5');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 0.0,
			maxLatitude: -95.5,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid maximum latitude (too high).
	 * Requirements: 2.4, 9.4
	 */
	public function test_bounds_rejects_invalid_max_latitude_too_high(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-90.0');
		$this->expectExceptionMessage('90.0');
		$this->expectExceptionMessage('95.5');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 0.0,
			maxLatitude: 95.5,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid minimum longitude (too low).
	 * Requirements: 2.5, 9.4
	 */
	public function test_bounds_rejects_invalid_min_longitude_too_low(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('-185.5');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: -185.5,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid minimum longitude (at or above 180).
	 * Requirements: 2.5, 9.4
	 */
	public function test_bounds_rejects_invalid_min_longitude_too_high(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('180.0');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 180.0,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects invalid maximum longitude (too low).
	 * Requirements: 2.5, 9.4
	 */
	public function test_bounds_rejects_invalid_max_longitude_too_low(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('-185.5');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 0.0,
			maxLatitude: 50.0,
			maxLongitude: -185.5,
		);
	}

	/**
	 * Test Bounds rejects invalid maximum longitude (at or above 180).
	 * Requirements: 2.5, 9.4
	 */
	public function test_bounds_rejects_invalid_max_longitude_too_high(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('-180.0');
		$this->expectExceptionMessage('180.0');
		$this->expectExceptionMessage('185.5');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 0.0,
			maxLatitude: 50.0,
			maxLongitude: 185.5,
		);
	}

	/**
	 * Test Bounds rejects minlat > maxlat.
	 * Requirements: 2.6, 9.3, 9.4
	 */
	public function test_bounds_rejects_min_latitude_greater_than_max(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Minimum latitude');
		$this->expectExceptionMessage('maximum latitude');
		$this->expectExceptionMessage('50.0');
		$this->expectExceptionMessage('40.0');

		new Bounds(
			minLatitude: 50.0,
			minLongitude: 0.0,
			maxLatitude: 40.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds rejects minlon > maxlon.
	 * Requirements: 2.7, 9.3, 9.4
	 */
	public function test_bounds_rejects_min_longitude_greater_than_max(): void
	{
		// Arrange & Act & Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Minimum longitude');
		$this->expectExceptionMessage('maximum longitude');
		$this->expectExceptionMessage('20.0');
		$this->expectExceptionMessage('10.0');

		new Bounds(
			minLatitude: 0.0,
			minLongitude: 20.0,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);
	}

	/**
	 * Test Bounds with boundary latitude values.
	 * Requirements: 2.2, 2.4, 2.5
	 */
	public function test_bounds_with_boundary_latitude_values(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: -90.0,
			minLongitude: -180.0,
			maxLatitude: 90.0,
			maxLongitude: 179.999999,
		);

		// Assert
		$this->assertEquals(-90.0, $bounds->minLatitude);
		$this->assertEquals(-180.0, $bounds->minLongitude);
		$this->assertEquals(90.0, $bounds->maxLatitude);
		$this->assertEquals(179.999999, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds with zero coordinates.
	 * Requirements: 2.2
	 */
	public function test_bounds_with_zero_coordinates(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: 0.0,
			minLongitude: 0.0,
			maxLatitude: 0.0,
			maxLongitude: 0.0,
		);

		// Assert
		$this->assertEquals(0.0, $bounds->minLatitude);
		$this->assertEquals(0.0, $bounds->minLongitude);
		$this->assertEquals(0.0, $bounds->maxLatitude);
		$this->assertEquals(0.0, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds with negative coordinates.
	 * Requirements: 2.2
	 */
	public function test_bounds_with_negative_coordinates(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: -45.5,
			minLongitude: -120.3,
			maxLatitude: -30.2,
			maxLongitude: -100.1,
		);

		// Assert
		$this->assertEquals(-45.5, $bounds->minLatitude);
		$this->assertEquals(-120.3, $bounds->minLongitude);
		$this->assertEquals(-30.2, $bounds->maxLatitude);
		$this->assertEquals(-100.1, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds spanning hemispheres.
	 * Requirements: 2.2
	 */
	public function test_bounds_spanning_hemispheres(): void
	{
		// Arrange & Act - Spanning equator and prime meridian
		$bounds = new Bounds(
			minLatitude: -10.0,
			minLongitude: -10.0,
			maxLatitude: 10.0,
			maxLongitude: 10.0,
		);

		// Assert
		$this->assertEquals(-10.0, $bounds->minLatitude);
		$this->assertEquals(-10.0, $bounds->minLongitude);
		$this->assertEquals(10.0, $bounds->maxLatitude);
		$this->assertEquals(10.0, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds for a single point (min equals max).
	 * Requirements: 2.2
	 */
	public function test_bounds_for_single_point(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: 50.0,
			minLongitude: 10.0,
			maxLatitude: 50.0,
			maxLongitude: 10.0,
		);

		// Assert
		$this->assertEquals(50.0, $bounds->minLatitude);
		$this->assertEquals(10.0, $bounds->minLongitude);
		$this->assertEquals(50.0, $bounds->maxLatitude);
		$this->assertEquals(10.0, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds serialization to array.
	 * Requirements: 2.7
	 */
	public function test_bounds_serializes_to_array_correctly(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: 18.814543,
			maxLatitude: 49.090543,
			maxLongitude: 18.886939,
		);

		// Act
		$array = $bounds->toArray();

		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('minlat', $array);
		$this->assertArrayHasKey('minlon', $array);
		$this->assertArrayHasKey('maxlat', $array);
		$this->assertArrayHasKey('maxlon', $array);
		$this->assertEquals(49.072489, $array['minlat']);
		$this->assertEquals(18.814543, $array['minlon']);
		$this->assertEquals(49.090543, $array['maxlat']);
		$this->assertEquals(18.886939, $array['maxlon']);
	}

	/**
	 * Test Bounds serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_bounds_serializes_to_xml_correctly(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: 18.814543,
			maxLatitude: 49.090543,
			maxLongitude: 18.886939,
		);

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = BoundsParser::toXML($bounds, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<bounds', $xml);
		$this->assertStringContainsString('minlat="49.072489"', $xml);
		$this->assertStringContainsString('minlon="18.814543"', $xml);
		$this->assertStringContainsString('maxlat="49.090543"', $xml);
		$this->assertStringContainsString('maxlon="18.886939"', $xml);
	}

	/**
	 * Test Bounds with very precise coordinates.
	 * Requirements: 2.2
	 */
	public function test_bounds_with_precise_coordinates(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: 49.072489123456,
			minLongitude: 18.814543987654,
			maxLatitude: 49.090543456789,
			maxLongitude: 18.886939321098,
		);

		// Assert
		$this->assertEquals(49.072489123456, $bounds->minLatitude);
		$this->assertEquals(18.814543987654, $bounds->minLongitude);
		$this->assertEquals(49.090543456789, $bounds->maxLatitude);
		$this->assertEquals(18.886939321098, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds implements Summarizable interface.
	 * Requirements: 2.2
	 */
	public function test_bounds_implements_summarizable(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: 18.814543,
			maxLatitude: 49.090543,
			maxLongitude: 18.886939,
		);

		// Assert
		$this->assertInstanceOf(\phpGPX\Models\Summarizable::class, $bounds);
	}

	/**
	 * Test Bounds toArray returns correct structure.
	 * Requirements: 2.7
	 */
	public function test_bounds_to_array_has_correct_structure(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: 18.814543,
			maxLatitude: 49.090543,
			maxLongitude: 18.886939,
		);

		// Act
		$array = $bounds->toArray();

		// Assert
		$this->assertCount(4, $array);
		$this->assertArrayHasKey('minlat', $array);
		$this->assertArrayHasKey('minlon', $array);
		$this->assertArrayHasKey('maxlat', $array);
		$this->assertArrayHasKey('maxlon', $array);
	}

	/**
	 * Test Bounds with world-spanning coordinates.
	 * Requirements: 2.2, 2.4, 2.5
	 */
	public function test_bounds_with_world_spanning_coordinates(): void
	{
		// Arrange & Act - Entire world
		$bounds = new Bounds(
			minLatitude: -90.0,
			minLongitude: -180.0,
			maxLatitude: 90.0,
			maxLongitude: 179.999999,
		);

		// Assert
		$this->assertEquals(-90.0, $bounds->minLatitude);
		$this->assertEquals(-180.0, $bounds->minLongitude);
		$this->assertEquals(90.0, $bounds->maxLatitude);
		$this->assertEquals(179.999999, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds XML serialization preserves precision.
	 * Requirements: 2.5
	 */
	public function test_bounds_xml_serialization_preserves_precision(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489123,
			minLongitude: 18.814543987,
			maxLatitude: 49.090543456,
			maxLongitude: 18.886939321,
		);

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = BoundsParser::toXML($bounds, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert - Check that precision is maintained (at least 6 decimal places)
		$this->assertStringContainsString('49.072489', $xml);
		$this->assertStringContainsString('18.814543', $xml);
		$this->assertStringContainsString('49.090543', $xml);
		$this->assertStringContainsString('18.886939', $xml);
	}

	/**
	 * Property test: Bounds requires all four coordinates.
	 *
	 * **Feature: gpx-schema-compliance, Property 5: Bounds requires all four coordinates**
	 * **Validates: Requirements 2.1, 2.2**
	 *
	 * For any valid coordinate values, Bounds construction should succeed when all four
	 * coordinates are provided and fail with TypeError when any are missing.
	 */
	public function test_property_bounds_requires_all_four_coordinates(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),   // minlat
				Generators::choose(-180, 179), // minlon
				Generators::choose(-90, 90),   // maxlat
				Generators::choose(-180, 179),  // maxlon
			)
			->withMaxSize(100)
			->then(function ($minLat, $minLon, $maxLat, $maxLon): void {
				// Convert to floats
				$minLat = (float) $minLat;
				$minLon = (float) $minLon;
				$maxLat = (float) $maxLat;
				$maxLon = (float) $maxLon;

				// Ensure logical consistency
				if ($minLat > $maxLat) {
					[$minLat, $maxLat] = [$maxLat, $minLat];
				}
				if ($minLon > $maxLon) {
					[$minLon, $maxLon] = [$maxLon, $minLon];
				}

				// Should succeed with all four coordinates
				$bounds = new Bounds($minLat, $minLon, $maxLat, $maxLon);

				$this->assertInstanceOf(Bounds::class, $bounds);
				$this->assertEquals($minLat, $bounds->minLatitude);
				$this->assertEquals($minLon, $bounds->minLongitude);
				$this->assertEquals($maxLat, $bounds->maxLatitude);
				$this->assertEquals($maxLon, $bounds->maxLongitude);
			});
	}

	/**
	 * Property test: Bounds logical consistency - latitude.
	 *
	 * **Feature: gpx-schema-compliance, Property 7: Bounds logical consistency - latitude**
	 * **Validates: Requirements 2.6**
	 *
	 * For any latitude values where minlat > maxlat, Bounds construction should throw
	 * InvalidArgumentException with a message indicating the constraint violation.
	 */
	public function test_property_bounds_latitude_consistency(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),  // minlat
				Generators::choose(-90, 90),   // maxlat
			)
			->withMaxSize(100)
			->then(function ($minLat, $maxLat): void {
				$minLat = (float) $minLat;
				$maxLat = (float) $maxLat;

				if ($minLat > $maxLat) {
					// Should throw exception
					try {
						new Bounds($minLat, 0.0, $maxLat, 10.0);
						$this->fail('Expected InvalidArgumentException for minlat > maxlat');
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString('Minimum latitude', $e->getMessage());
						$this->assertStringContainsString('maximum latitude', $e->getMessage());
						$this->assertStringContainsString((string) $minLat, $e->getMessage());
						$this->assertStringContainsString((string) $maxLat, $e->getMessage());
					}
				} else {
					// Should succeed
					$bounds = new Bounds($minLat, 0.0, $maxLat, 10.0);
					$this->assertEquals($minLat, $bounds->minLatitude);
					$this->assertEquals($maxLat, $bounds->maxLatitude);
				}
			});
	}

	/**
	 * Property test: Bounds logical consistency - longitude.
	 *
	 * **Feature: gpx-schema-compliance, Property 8: Bounds logical consistency - longitude**
	 * **Validates: Requirements 2.7**
	 *
	 * For any longitude values where minlon > maxlon, Bounds construction should throw
	 * InvalidArgumentException with a message indicating the constraint violation.
	 */
	public function test_property_bounds_longitude_consistency(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-180, 179),  // minlon
				Generators::choose(-180, 179),   // maxlon
			)
			->withMaxSize(100)
			->then(function ($minLon, $maxLon): void {
				$minLon = (float) $minLon;
				$maxLon = (float) $maxLon;

				if ($minLon > $maxLon) {
					// Should throw exception
					try {
						new Bounds(0.0, $minLon, 50.0, $maxLon);
						$this->fail('Expected InvalidArgumentException for minlon > maxlon');
					} catch (InvalidArgumentException $e) {
						$this->assertStringContainsString('Minimum longitude', $e->getMessage());
						$this->assertStringContainsString('maximum longitude', $e->getMessage());
						$this->assertStringContainsString((string) $minLon, $e->getMessage());
						$this->assertStringContainsString((string) $maxLon, $e->getMessage());
					}
				} else {
					// Should succeed
					$bounds = new Bounds(0.0, $minLon, 50.0, $maxLon);
					$this->assertEquals($minLon, $bounds->minLongitude);
					$this->assertEquals($maxLon, $bounds->maxLongitude);
				}
			});
	}

	/**
	 * Property test: Bounds validation error messages.
	 *
	 * **Feature: gpx-schema-compliance, Property 26: Bounds validation error messages**
	 * **Validates: Requirements 9.3**
	 *
	 * For any invalid Bounds construction, the error message should clearly indicate
	 * which constraint was violated (range or logical consistency).
	 */
	public function test_property_bounds_validation_error_messages(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-200, 200),  // latitude value
				Generators::choose(-300, 300),   // longitude value
			)
			->withMaxSize(100)
			->then(function ($lat, $lon): void {
				$lat = (float) $lat;
				$lon = (float) $lon;

				// Test invalid latitude
				if ($lat < -90.0 || $lat > 90.0) {
					try {
						new Bounds($lat, 0.0, 50.0, 10.0);
						$this->fail('Expected InvalidArgumentException for invalid latitude');
					} catch (InvalidArgumentException $e) {
						// Should mention the valid range
						$this->assertStringContainsString('-90.0', $e->getMessage());
						$this->assertStringContainsString('90.0', $e->getMessage());
					}
				}

				// Test invalid longitude
				if ($lon < -180.0 || $lon >= 180.0) {
					try {
						new Bounds(0.0, $lon, 50.0, 10.0);
						$this->fail('Expected InvalidArgumentException for invalid longitude');
					} catch (InvalidArgumentException $e) {
						// Should mention the valid range
						$this->assertStringContainsString('-180.0', $e->getMessage());
						$this->assertStringContainsString('180.0', $e->getMessage());
					}
				}
			});
	}
}
