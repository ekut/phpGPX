<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models\Extensions;

use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\Extensions\TrackPointExtensionParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for TrackPointExtension model.
 * Tests extension creation, property storage, and serialization.
 */
final class TrackPointExtensionTest extends TestCase
{
	/**
	 * Test TrackPointExtension creation.
	 * Requirements: 2.2
	 */
	public function test_extension_can_be_created(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		
		// Assert
		$this->assertInstanceOf(TrackPointExtension::class, $extension);
		$this->assertEquals(TrackPointExtension::EXTENSION_NAMESPACE, $extension->namespace);
		$this->assertEquals(TrackPointExtension::EXTENSION_NAME, $extension->extensionName);
	}

	/**
	 * Test TrackPointExtension with temperature data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_temperature_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->aTemp = 25.5;
		$extension->wTemp = 18.3;
		
		// Assert
		$this->assertEquals(25.5, $extension->aTemp);
		$this->assertEquals(18.3, $extension->wTemp);
	}

	/**
	 * Test TrackPointExtension with depth data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_depth_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->depth = 10.5;
		
		// Assert
		$this->assertEquals(10.5, $extension->depth);
	}

	/**
	 * Test TrackPointExtension with heart rate data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_heart_rate_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->hr = 145.0;
		
		// Assert
		$this->assertEquals(145.0, $extension->hr);
	}

	/**
	 * Test TrackPointExtension with cadence data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_cadence_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->cad = 85.0;
		
		// Assert
		$this->assertEquals(85.0, $extension->cad);
	}

	/**
	 * Test TrackPointExtension with speed data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_speed_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->speed = 5.5;
		
		// Assert
		$this->assertEquals(5.5, $extension->speed);
	}

	/**
	 * Test TrackPointExtension with course data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_course_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->course = 180;
		
		// Assert
		$this->assertEquals(180, $extension->course);
	}

	/**
	 * Test TrackPointExtension with bearing data.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_bearing_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->bearing = 90;
		
		// Assert
		$this->assertEquals(90, $extension->bearing);
	}

	/**
	 * Test TrackPointExtension with all properties.
	 * Requirements: 2.2
	 */
	public function test_extension_stores_all_properties(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->aTemp = 25.5;
		$extension->wTemp = 18.3;
		$extension->depth = 10.5;
		$extension->hr = 145.0;
		$extension->cad = 85.0;
		$extension->speed = 5.5;
		$extension->course = 180;
		$extension->bearing = 90;
		
		// Assert
		$this->assertEquals(25.5, $extension->aTemp);
		$this->assertEquals(18.3, $extension->wTemp);
		$this->assertEquals(10.5, $extension->depth);
		$this->assertEquals(145.0, $extension->hr);
		$this->assertEquals(85.0, $extension->cad);
		$this->assertEquals(5.5, $extension->speed);
		$this->assertEquals(180, $extension->course);
		$this->assertEquals(90, $extension->bearing);
	}

	/**
	 * Test TrackPointExtension initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_extension_initialization_has_null_values(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		
		// Assert
		$this->assertNull($extension->aTemp);
		$this->assertNull($extension->wTemp);
		$this->assertNull($extension->depth);
		$this->assertNull($extension->hr);
		$this->assertNull($extension->cad);
		$this->assertNull($extension->speed);
		$this->assertNull($extension->course);
		$this->assertNull($extension->bearing);
	}

	/**
	 * Test TrackPointExtension serialization to array.
	 * Requirements: 2.7
	 */
	public function test_extension_serializes_to_array_correctly(): void
	{
		// Arrange
		$extension = new TrackPointExtension();
		$extension->aTemp = 25.5;
		$extension->hr = 145.0;
		$extension->cad = 85.0;
		$extension->speed = 5.5;
		
		// Act
		$array = $extension->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals(25.5, $array['aTemp']);
		$this->assertEquals(145.0, $array['hr']);
		$this->assertEquals(85.0, $array['cad']);
		$this->assertEquals(5.5, $array['speed']);
	}

	/**
	 * Test TrackPointExtension serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_extension_serializes_null_values_correctly(): void
	{
		// Arrange
		$extension = new TrackPointExtension();
		
		// Act
		$array = $extension->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertNull($array['aTemp']);
		$this->assertNull($array['wTemp']);
		$this->assertNull($array['depth']);
		$this->assertNull($array['hr']);
		$this->assertNull($array['cad']);
		$this->assertNull($array['speed']);
		$this->assertNull($array['course']);
		$this->assertNull($array['bearing']);
	}

	/**
	 * Test TrackPointExtension serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_extension_serializes_to_xml_correctly(): void
	{
		// Arrange
		$extension = new TrackPointExtension();
		$extension->aTemp = 25.5;
		$extension->hr = 145.0;
		$extension->cad = 85.0;
		$extension->speed = 5.5;
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = TrackPointExtensionParser::toXML($extension, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('TrackPointExtension', $xml);
		$this->assertStringContainsString('<gpxtpx:atemp>25.5</gpxtpx:atemp>', $xml);
		$this->assertStringContainsString('<gpxtpx:hr>145</gpxtpx:hr>', $xml);
		$this->assertStringContainsString('<gpxtpx:cad>85</gpxtpx:cad>', $xml);
		$this->assertStringContainsString('<gpxtpx:speed>5.5</gpxtpx:speed>', $xml);
	}

	/**
	 * Test TrackPointExtension serialization to XML with all properties.
	 * Requirements: 2.5
	 */
	public function test_extension_xml_serialization_includes_all_properties(): void
	{
		// Arrange
		$extension = new TrackPointExtension();
		$extension->aTemp = 25.5;
		$extension->wTemp = 18.3;
		$extension->depth = 10.5;
		$extension->hr = 145.0;
		$extension->cad = 85.0;
		$extension->speed = 5.5;
		$extension->course = 180;
		$extension->bearing = 90;
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = TrackPointExtensionParser::toXML($extension, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<gpxtpx:atemp>25.5</gpxtpx:atemp>', $xml);
		$this->assertStringContainsString('<gpxtpx:wtemp>18.3</gpxtpx:wtemp>', $xml);
		$this->assertStringContainsString('<gpxtpx:depth>10.5</gpxtpx:depth>', $xml);
		$this->assertStringContainsString('<gpxtpx:hr>145</gpxtpx:hr>', $xml);
		$this->assertStringContainsString('<gpxtpx:cad>85</gpxtpx:cad>', $xml);
		$this->assertStringContainsString('<gpxtpx:speed>5.5</gpxtpx:speed>', $xml);
		$this->assertStringContainsString('<gpxtpx:course>180</gpxtpx:course>', $xml);
		$this->assertStringContainsString('<gpxtpx:bearing>90</gpxtpx:bearing>', $xml);
	}

	/**
	 * Test TrackPointExtension serialization to XML excludes null values.
	 * Requirements: 2.5
	 */
	public function test_extension_xml_serialization_excludes_null_values(): void
	{
		// Arrange
		$extension = new TrackPointExtension();
		$extension->hr = 145.0;
		// Other properties remain null
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = TrackPointExtensionParser::toXML($extension, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<gpxtpx:hr>145</gpxtpx:hr>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:atemp>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:wtemp>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:depth>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:cad>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:speed>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:course>', $xml);
		$this->assertStringNotContainsString('<gpxtpx:bearing>', $xml);
	}

	/**
	 * Test TrackPointExtension with zero values.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_zero_values(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->aTemp = 0.0;
		$extension->hr = 0.0;
		$extension->speed = 0.0;
		$extension->course = 0;
		
		// Assert
		$this->assertEquals(0.0, $extension->aTemp);
		$this->assertEquals(0.0, $extension->hr);
		$this->assertEquals(0.0, $extension->speed);
		$this->assertEquals(0, $extension->course);
	}

	/**
	 * Test TrackPointExtension with negative temperature.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_negative_temperature(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->aTemp = -10.5;
		$extension->wTemp = -5.0;
		
		// Assert
		$this->assertEquals(-10.5, $extension->aTemp);
		$this->assertEquals(-5.0, $extension->wTemp);
	}

	/**
	 * Test TrackPointExtension with maximum course value.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_maximum_course_value(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->course = 359;
		
		// Assert
		$this->assertEquals(359, $extension->course);
	}

	/**
	 * Test TrackPointExtension with maximum bearing value.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_maximum_bearing_value(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->bearing = 359;
		
		// Assert
		$this->assertEquals(359, $extension->bearing);
	}

	/**
	 * Test TrackPointExtension namespace constants.
	 * Requirements: 2.2
	 */
	public function test_extension_has_correct_namespace_constants(): void
	{
		// Assert
		$this->assertEquals(
			'http://www.garmin.com/xmlschemas/TrackPointExtension/v2',
			TrackPointExtension::EXTENSION_NAMESPACE
		);
		$this->assertEquals(
			'http://www.garmin.com/xmlschemas/TrackPointExtensionv2.xsd',
			TrackPointExtension::EXTENSION_NAMESPACE_XSD
		);
		$this->assertEquals(
			'TrackPointExtension',
			TrackPointExtension::EXTENSION_NAME
		);
		$this->assertEquals(
			'gpxtpx',
			TrackPointExtension::EXTENSION_NAMESPACE_PREFIX
		);
	}

	/**
	 * Test TrackPointExtension v1 namespace constants.
	 * Requirements: 2.2
	 */
	public function test_extension_has_v1_namespace_constants(): void
	{
		// Assert
		$this->assertEquals(
			'http://www.garmin.com/xmlschemas/TrackPointExtension/v1',
			TrackPointExtension::EXTENSION_V1_NAMESPACE
		);
		$this->assertEquals(
			'http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd',
			TrackPointExtension::EXTENSION_V1_NAMESPACE_XSD
		);
	}

	/**
	 * Test TrackPointExtension with typical cycling data.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_typical_cycling_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->hr = 155.0;      // Heart rate
		$extension->cad = 90.0;      // Cadence (RPM)
		$extension->speed = 8.33;    // Speed (m/s, ~30 km/h)
		$extension->aTemp = 22.0;    // Temperature
		
		// Assert
		$this->assertEquals(155.0, $extension->hr);
		$this->assertEquals(90.0, $extension->cad);
		$this->assertEquals(8.33, $extension->speed);
		$this->assertEquals(22.0, $extension->aTemp);
	}

	/**
	 * Test TrackPointExtension with typical diving data.
	 * Requirements: 2.2
	 */
	public function test_extension_handles_typical_diving_data(): void
	{
		// Arrange & Act
		$extension = new TrackPointExtension();
		$extension->depth = 15.5;    // Depth in meters
		$extension->wTemp = 18.0;    // Water temperature
		
		// Assert
		$this->assertEquals(15.5, $extension->depth);
		$this->assertEquals(18.0, $extension->wTemp);
	}
}
