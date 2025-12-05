<?php

declare(strict_types=1);

/**
 * Unit tests for BoundsParser
 */

namespace phpGPX\Tests\Unit\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Bounds;
use phpGPX\Parsers\BoundsParser;

class BoundsParserTest extends AbstractParserTest
{
	protected $testModelClass = Bounds::class;

	protected $testParserClass = BoundsParser::class;

	/**
	 * @var Bounds
	 */
	protected $testModelInstance;

	public static function createTestInstance(): Bounds
	{
		return new Bounds(54.0, 9.0, 55.0, 10.0);
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$bounds = BoundsParser::parse($this->testXmlFile->bounds);

		$this->assertNotEmpty($bounds);
		$this->assertInstanceOf(Bounds::class, $bounds);

		// Verify all attributes
		$this->assertEquals($this->testModelInstance->minLatitude, $bounds->minLatitude);
		$this->assertEquals($this->testModelInstance->minLongitude, $bounds->minLongitude);
		$this->assertEquals($this->testModelInstance->maxLatitude, $bounds->maxLatitude);
		$this->assertEquals($this->testModelInstance->maxLongitude, $bounds->maxLongitude);
	}

	/**
	 * Test parsing valid bounds XML with all attributes.
	 * Requirements: 3.2
	 */
	public function test_parse_valid_bounds_with_all_attributes(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="45.5" minlon="8.5" maxlat="46.5" maxlon="9.5"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(45.5, $bounds->minLatitude);
		$this->assertEquals(8.5, $bounds->minLongitude);
		$this->assertEquals(46.5, $bounds->maxLatitude);
		$this->assertEquals(9.5, $bounds->maxLongitude);
	}

	/**
	 * Test parsing bounds with negative coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_bounds_with_negative_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="-45.0" minlon="-120.0" maxlat="-30.0" maxlon="-100.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(-45.0, $bounds->minLatitude);
		$this->assertEquals(-120.0, $bounds->minLongitude);
		$this->assertEquals(-30.0, $bounds->maxLatitude);
		$this->assertEquals(-100.0, $bounds->maxLongitude);
	}

	/**
	 * Test parsing bounds spanning hemispheres.
	 * Requirements: 3.2
	 */
	public function test_parse_bounds_spanning_hemispheres(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="-10.0" minlon="-20.0" maxlat="10.0" maxlon="20.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(-10.0, $bounds->minLatitude);
		$this->assertEquals(-20.0, $bounds->minLongitude);
		$this->assertEquals(10.0, $bounds->maxLatitude);
		$this->assertEquals(20.0, $bounds->maxLongitude);
	}

	/**
	 * Test parsing bounds with extreme valid coordinates.
	 * Requirements: 3.2, 2.4, 2.5
	 */
	public function test_parse_bounds_with_extreme_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="-90.0" minlon="-180.0" maxlat="90.0" maxlon="179.999999"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(-90.0, $bounds->minLatitude);
		$this->assertEquals(-180.0, $bounds->minLongitude);
		$this->assertEquals(90.0, $bounds->maxLatitude);
		$this->assertEquals(179.999999, $bounds->maxLongitude);
	}

	/**
	 * Test parsing bounds with missing minlat attribute returns null.
	 * Per GPX 1.1 schema, all four attributes are required.
	 * Requirements: 2.1, 2.2
	 */
	public function test_parse_bounds_with_missing_minlat(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlon="9.0" maxlat="55.0" maxlon="10.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing bounds with missing minlon attribute returns null.
	 * Per GPX 1.1 schema, all four attributes are required.
	 * Requirements: 2.1, 2.2
	 */
	public function test_parse_bounds_with_missing_minlon(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="54.0" maxlat="55.0" maxlon="10.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing bounds with missing maxlat attribute returns null.
	 * Per GPX 1.1 schema, all four attributes are required.
	 * Requirements: 2.1, 2.2
	 */
	public function test_parse_bounds_with_missing_maxlat(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="54.0" minlon="9.0" maxlon="10.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing bounds with missing maxlon attribute returns null.
	 * Per GPX 1.1 schema, all four attributes are required.
	 * Requirements: 2.1, 2.2
	 */
	public function test_parse_bounds_with_missing_maxlon(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="54.0" minlon="9.0" maxlat="55.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing bounds with all attributes missing returns null.
	 * Per GPX 1.1 schema, all four attributes are required.
	 * Requirements: 2.1, 2.2
	 */
	public function test_parse_bounds_with_all_attributes_missing(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing invalid element (not bounds) returns null.
	 * Requirements: 3.5
	 */
	public function test_parse_invalid_element_returns_null(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<notbounds minlat="54.0" minlon="9.0" maxlat="55.0" maxlon="10.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertNull($bounds);
	}

	/**
	 * Test parsing bounds with zero coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_bounds_with_zero_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="0.0" minlon="0.0" maxlat="0.0" maxlon="0.0"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(0.0, $bounds->minLatitude);
		$this->assertEquals(0.0, $bounds->minLongitude);
		$this->assertEquals(0.0, $bounds->maxLatitude);
		$this->assertEquals(0.0, $bounds->maxLongitude);
	}

	/**
	 * Test parsing bounds with decimal precision.
	 * Requirements: 3.2
	 */
	public function test_parse_bounds_with_high_precision(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<bounds minlat="54.123456789" minlon="9.987654321" maxlat="55.111111111" maxlon="10.222222222"/>
		');

		$bounds = BoundsParser::parse($xml);

		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertEquals(54.123456789, $bounds->minLatitude);
		$this->assertEquals(9.987654321, $bounds->minLongitude);
		$this->assertEquals(55.111111111, $bounds->maxLatitude);
		$this->assertEquals(10.222222222, $bounds->maxLongitude);
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return BoundsParser::toXML($this->testModelInstance, $document);
	}
}
