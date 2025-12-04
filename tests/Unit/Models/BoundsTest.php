<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Bounds;
use phpGPX\Parsers\BoundsParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Bounds model.
 * Tests Bounds creation, validation, and serialization.
 */
final class BoundsTest extends TestCase
{
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
			maxLongitude: 18.886939
		);
		
		// Assert
		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(49.072489, $bounds->minLatitude);
		$this->assertEquals(18.814543, $bounds->minLongitude);
		$this->assertEquals(49.090543, $bounds->maxLatitude);
		$this->assertEquals(18.886939, $bounds->maxLongitude);
	}

	/**
	 * Test Bounds creation with null values.
	 * Requirements: 2.2
	 */
	public function test_bounds_can_be_created_with_null_values(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: null,
			minLongitude: null,
			maxLatitude: null,
			maxLongitude: null
		);
		
		// Assert
		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertNull($bounds->minLatitude);
		$this->assertNull($bounds->minLongitude);
		$this->assertNull($bounds->maxLatitude);
		$this->assertNull($bounds->maxLongitude);
	}

	/**
	 * Test Bounds with partial null values.
	 * Requirements: 2.2
	 */
	public function test_bounds_with_partial_null_values(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: null,
			maxLatitude: 49.090543,
			maxLongitude: null
		);
		
		// Assert
		$this->assertEquals(49.072489, $bounds->minLatitude);
		$this->assertNull($bounds->minLongitude);
		$this->assertEquals(49.090543, $bounds->maxLatitude);
		$this->assertNull($bounds->maxLongitude);
	}

	/**
	 * Test Bounds with boundary latitude values.
	 * Requirements: 2.2
	 */
	public function test_bounds_with_boundary_latitude_values(): void
	{
		// Arrange & Act
		$bounds = new Bounds(
			minLatitude: -90.0,
			minLongitude: -180.0,
			maxLatitude: 90.0,
			maxLongitude: 180.0
		);
		
		// Assert
		$this->assertEquals(-90.0, $bounds->minLatitude);
		$this->assertEquals(-180.0, $bounds->minLongitude);
		$this->assertEquals(90.0, $bounds->maxLatitude);
		$this->assertEquals(180.0, $bounds->maxLongitude);
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
			maxLongitude: 0.0
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
			maxLongitude: -100.1
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
			maxLongitude: 10.0
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
			maxLongitude: 10.0
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
			maxLongitude: 18.886939
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
	 * Test Bounds serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_bounds_serializes_null_values_to_array(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: null,
			minLongitude: null,
			maxLatitude: null,
			maxLongitude: null
		);
		
		// Act
		$array = $bounds->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertNull($array['minlat']);
		$this->assertNull($array['minlon']);
		$this->assertNull($array['maxlat']);
		$this->assertNull($array['maxlon']);
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
			maxLongitude: 18.886939
		);
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
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
	 * Test Bounds serialization to XML with null values.
	 * Requirements: 2.5
	 */
	public function test_bounds_serializes_to_xml_with_null_values(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: null,
			minLongitude: null,
			maxLatitude: null,
			maxLongitude: null
		);
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = BoundsParser::toXML($bounds, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<bounds', $xml);
		$this->assertStringNotContainsString('minlat=', $xml);
		$this->assertStringNotContainsString('minlon=', $xml);
		$this->assertStringNotContainsString('maxlat=', $xml);
		$this->assertStringNotContainsString('maxlon=', $xml);
	}

	/**
	 * Test Bounds serialization to XML with partial null values.
	 * Requirements: 2.5
	 */
	public function test_bounds_serializes_to_xml_with_partial_null_values(): void
	{
		// Arrange
		$bounds = new Bounds(
			minLatitude: 49.072489,
			minLongitude: null,
			maxLatitude: 49.090543,
			maxLongitude: null
		);
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = BoundsParser::toXML($bounds, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<bounds', $xml);
		$this->assertStringContainsString('minlat="49.072489"', $xml);
		$this->assertStringNotContainsString('minlon=', $xml);
		$this->assertStringContainsString('maxlat="49.090543"', $xml);
		$this->assertStringNotContainsString('maxlon=', $xml);
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
			maxLongitude: 18.886939321098
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
			maxLongitude: 18.886939
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
			maxLongitude: 18.886939
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
	 * Requirements: 2.2
	 */
	public function test_bounds_with_world_spanning_coordinates(): void
	{
		// Arrange & Act - Entire world
		$bounds = new Bounds(
			minLatitude: -90.0,
			minLongitude: -180.0,
			maxLatitude: 90.0,
			maxLongitude: 180.0
		);
		
		// Assert
		$this->assertEquals(-90.0, $bounds->minLatitude);
		$this->assertEquals(-180.0, $bounds->minLongitude);
		$this->assertEquals(90.0, $bounds->maxLatitude);
		$this->assertEquals(180.0, $bounds->maxLongitude);
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
			maxLongitude: 18.886939321
		);
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
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
}
