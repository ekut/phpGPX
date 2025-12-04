<?php

namespace phpGPX\Tests\Unit\Parsers;

use phpGPX\Models\Extensions;
use phpGPX\Models\Extensions\TrackPointExtension;
use phpGPX\Parsers\ExtensionParser;

class ExtensionParserTest extends AbstractParserTest
{
	protected $testModelClass = Extension::class;
	protected $testParserClass = ExtensionParser::class;

	/**
	 * @var Extension
	 */
	protected $testModelInstance;

	/**
	 * @return Extension
	 */
	public static function createTestInstance()
	{
		$trackpoint = new TrackPointExtension();
		$trackpoint->aTemp = (float) 14;
		$trackpoint->avgTemperature = (float) 14;
		$trackpoint->hr = (float) 152;
		$trackpoint->heartRate = (float) 152;

		$extensions = new Extensions();
		$extensions->trackPointExtension = $trackpoint;

		return $extensions;
	}

	protected function setUp(): void
    {
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse()
	{
		$extensions = ExtensionParser::parse($this->testXmlFile->extensions);

		$this->assertEquals($this->testModelInstance->unsupported, $extensions->unsupported);
		$this->assertEquals($this->testModelInstance->trackPointExtension, $extensions->trackPointExtension);

		$this->assertEquals($this->testModelInstance->toArray(), $extensions->toArray());
	}


	/**
	 * Returns output of ::toXML method of tested parser.
	 * @param \DOMDocument $document
	 * @return \DOMElement
	 */
	protected function convertToXML(\DOMDocument $document)
	{
		return ExtensionParser::toXML($this->testModelInstance, $document);
	}

	public function testToXML()
	{
		$document = new \DOMDocument("1.0", 'UTF-8');

		$root = $document->createElement("document");
		$root->appendChild($this->convertToXML($document));

		$attributes = [
			'xmlns' => 'http://www.topografix.com/GPX/1/1',
			'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation' => 'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd',
			'xmlns:gpxtpx' => 'http://www.garmin.com/xmlschemas/TrackPointExtension/v1',
			'xmlns:gpxx' => 'http://www.garmin.com/xmlschemas/GpxExtensions/v3',
		];

		foreach ($attributes as $key => $value) {
			$attribute = $document->createAttribute($key);
			$attribute->value = $value;
			$root->appendChild($attribute);
		}

		$document->appendChild($root);

		$this->assertXmlStringEqualsXmlString($this->testXmlFile->asXML(), $document->saveXML());
	}

	/**
	 * Test parsing Garmin TrackPointExtension with all fields.
	 * Requirements: 3.3
	 */
	public function test_parse_garmin_trackpoint_extension_with_all_fields(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v2">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:atemp>22.5</gpxtpx:atemp>
					<gpxtpx:wtemp>18.0</gpxtpx:wtemp>
					<gpxtpx:depth>5.5</gpxtpx:depth>
					<gpxtpx:hr>145</gpxtpx:hr>
					<gpxtpx:cad>85</gpxtpx:cad>
					<gpxtpx:speed>3.5</gpxtpx:speed>
					<gpxtpx:course>180</gpxtpx:course>
					<gpxtpx:bearing>90</gpxtpx:bearing>
				</gpxtpx:TrackPointExtension>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		$this->assertInstanceOf(TrackPointExtension::class, $extensions->trackPointExtension);
		
		$tpe = $extensions->trackPointExtension;
		$this->assertEquals(22.5, $tpe->aTemp);
		$this->assertEquals(18.0, $tpe->wTemp);
		$this->assertEquals(5.5, $tpe->depth);
		$this->assertEquals(145.0, $tpe->hr);
		$this->assertEquals(85.0, $tpe->cad);
		$this->assertEquals(3.5, $tpe->speed);
		$this->assertEquals(180, $tpe->course);
		$this->assertEquals(90, $tpe->bearing);
	}

	/**
	 * Test parsing Garmin TrackPointExtension v1 namespace.
	 * Requirements: 3.3
	 */
	public function test_parse_garmin_trackpoint_extension_v1_namespace(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:atemp>20.0</gpxtpx:atemp>
					<gpxtpx:hr>150</gpxtpx:hr>
				</gpxtpx:TrackPointExtension>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		$this->assertEquals(20.0, $extensions->trackPointExtension->aTemp);
		$this->assertEquals(150.0, $extensions->trackPointExtension->hr);
	}

	/**
	 * Test parsing Garmin TrackPointExtension with partial fields.
	 * Requirements: 3.3
	 */
	public function test_parse_garmin_trackpoint_extension_with_partial_fields(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v2">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:hr>160</gpxtpx:hr>
					<gpxtpx:cad>90</gpxtpx:cad>
				</gpxtpx:TrackPointExtension>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		
		$tpe = $extensions->trackPointExtension;
		$this->assertEquals(160.0, $tpe->hr);
		$this->assertEquals(90.0, $tpe->cad);
		$this->assertNull($tpe->aTemp);
		$this->assertNull($tpe->wTemp);
		$this->assertNull($tpe->depth);
		$this->assertNull($tpe->speed);
		$this->assertNull($tpe->course);
		$this->assertNull($tpe->bearing);
	}

	/**
	 * Test parsing unknown extensions.
	 * Requirements: 3.3
	 */
	public function test_parse_unknown_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:custom="http://example.com/custom">
				<custom:customField>Custom Value</custom:customField>
				<custom:anotherField>Another Value</custom:anotherField>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNull($extensions->trackPointExtension);
		$this->assertNotEmpty($extensions->unsupported);
		$this->assertArrayHasKey('custom:customField', $extensions->unsupported);
		$this->assertArrayHasKey('custom:anotherField', $extensions->unsupported);
		$this->assertEquals('Custom Value', $extensions->unsupported['custom:customField']);
		$this->assertEquals('Another Value', $extensions->unsupported['custom:anotherField']);
	}

	/**
	 * Test parsing mixed known and unknown extensions.
	 * Requirements: 3.3
	 */
	public function test_parse_mixed_known_and_unknown_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v2" xmlns:custom="http://example.com/custom">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:hr>155</gpxtpx:hr>
				</gpxtpx:TrackPointExtension>
				<custom:customData>Test Data</custom:customData>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		$this->assertEquals(155.0, $extensions->trackPointExtension->hr);
		$this->assertNotEmpty($extensions->unsupported);
		$this->assertArrayHasKey('custom:customData', $extensions->unsupported);
		$this->assertEquals('Test Data', $extensions->unsupported['custom:customData']);
	}

	/**
	 * Test parsing empty extensions element.
	 * Requirements: 3.3
	 */
	public function test_parse_empty_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions></extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNull($extensions->trackPointExtension);
		$this->assertEmpty($extensions->unsupported);
	}

	/**
	 * Test parsing TrackPointExtension with zero values.
	 * Requirements: 3.3
	 */
	public function test_parse_trackpoint_extension_with_zero_values(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v2">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:atemp>0</gpxtpx:atemp>
					<gpxtpx:hr>0</gpxtpx:hr>
					<gpxtpx:cad>0</gpxtpx:cad>
					<gpxtpx:speed>0</gpxtpx:speed>
				</gpxtpx:TrackPointExtension>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		
		$tpe = $extensions->trackPointExtension;
		$this->assertEquals(0.0, $tpe->aTemp);
		$this->assertEquals(0.0, $tpe->hr);
		$this->assertEquals(0.0, $tpe->cad);
		$this->assertEquals(0.0, $tpe->speed);
	}

	/**
	 * Test parsing TrackPointExtension with negative temperature.
	 * Requirements: 3.3
	 */
	public function test_parse_trackpoint_extension_with_negative_temperature(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<extensions xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v2">
				<gpxtpx:TrackPointExtension>
					<gpxtpx:atemp>-15.5</gpxtpx:atemp>
					<gpxtpx:wtemp>-2.0</gpxtpx:wtemp>
				</gpxtpx:TrackPointExtension>
			</extensions>
		');

		$extensions = ExtensionParser::parse($xml);

		$this->assertInstanceOf(Extensions::class, $extensions);
		$this->assertNotNull($extensions->trackPointExtension);
		$this->assertEquals(-15.5, $extensions->trackPointExtension->aTemp);
		$this->assertEquals(-2.0, $extensions->trackPointExtension->wTemp);
	}
}
