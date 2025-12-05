<?php

declare(strict_types=1);
/**
 * @author miqwit
 */

namespace phpGPX\Tests\Unit\Parsers;

use DateTime;
use DOMDocument;
use DOMElement;
use phpGPX\Enums\PointType;
use phpGPX\Helpers\DateTimeHelper;
use phpGPX\Models\Point;
use phpGPX\Parsers\PointParser;

class PointParserTest extends ParserTestBase
{
	protected $testModelClass = Point::class;

	protected $testParserClass = PointParser::class;

	/**
	 * @var Point
	 */
	protected $testModelInstance;

	public static function createTestInstance(): Point
	{
		$point = new Point(Point::TRACKPOINT, 46.571948, 8.414757);
		$point->elevation = 2419;
		$point->time = DateTimeHelper::parseDateTime("2017-08-13T07:10:41.000Z");

		return $point;
	}

	public static function createTestInstanceWithValues(
		float $latitude,
		float $longitude,
		float $elevation,
		string $timeAsString,
	): Point {
		$point = new Point(Point::TRACKPOINT, $latitude, $longitude);
		$point->elevation = $elevation;
		$point->time = DateTimeHelper::parseDateTime($timeAsString);

		return $point;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$point = PointParser::parse($this->testXmlFile->trkpt);

		$this->assertNotEmpty($point);

		// Primitive attributes
		$this->assertEquals($this->testModelInstance->latitude, $point->latitude);
		$this->assertEquals($this->testModelInstance->longitude, $point->longitude);
		$this->assertEquals($this->testModelInstance->elevation, $point->elevation);
		$this->assertEquals($this->testModelInstance->time, $point->time);
	}

	/**
	 * Test parsing valid point XML with all required attributes.
	 * Requirements: 3.2
	 */
	public function test_parse_valid_trackpoint_with_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="54.9328621088893" lon="9.860624216140083"></trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(54.9328621088893, $point->latitude);
		$this->assertEquals(9.860624216140083, $point->longitude);
		$this->assertSame(PointType::TRACKPOINT, $point->getPointType());
	}

	/**
	 * Test parsing waypoint type.
	 * Requirements: 3.2
	 */
	public function test_parse_valid_waypoint(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<wpt lat="52.5200" lon="13.4050"></wpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertSame(PointType::WAYPOINT, $point->getPointType());
		$this->assertEquals(52.5200, $point->latitude);
		$this->assertEquals(13.4050, $point->longitude);
	}

	/**
	 * Test parsing routepoint type.
	 * Requirements: 3.2
	 */
	public function test_parse_valid_routepoint(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<rtept lat="48.8566" lon="2.3522"></rtept>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertSame(PointType::ROUTEPOINT, $point->getPointType());
		$this->assertEquals(48.8566, $point->latitude);
		$this->assertEquals(2.3522, $point->longitude);
	}

	/**
	 * Test parsing point with elevation and time.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_point_with_elevation_and_time(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="46.571948" lon="8.414757">
				<ele>2419.5</ele>
				<time>2017-08-13T07:10:41Z</time>
			</trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(46.571948, $point->latitude);
		$this->assertEquals(8.414757, $point->longitude);
		$this->assertEquals(2419.5, $point->elevation);
		$this->assertInstanceOf(DateTime::class, $point->time);
		$this->assertEquals('2017-08-13T07:10:41+00:00', $point->time->format('c'));
	}

	/**
	 * Test parsing point with all optional metadata fields.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_point_with_optional_metadata(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<wpt lat="50.0" lon="10.0">
				<ele>100.5</ele>
				<time>2023-01-15T12:30:00Z</time>
				<name>Test Waypoint</name>
				<cmt>Test comment</cmt>
				<desc>Test description</desc>
				<src>GPS Device</src>
				<sym>Flag</sym>
				<type>Summit</type>
			</wpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals('Test Waypoint', $point->name);
		$this->assertEquals('Test comment', $point->comment);
		$this->assertEquals('Test description', $point->description);
		$this->assertEquals('GPS Device', $point->source);
		$this->assertEquals('Flag', $point->symbol);
		$this->assertEquals('Summit', $point->type);
	}

	/**
	 * Test parsing point with GPS fix data.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_point_with_gps_fix_data(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="45.0" lon="9.0">
				<fix>3d</fix>
				<sat>8</sat>
				<hdop>1.2</hdop>
				<vdop>1.5</vdop>
				<pdop>2.0</pdop>
			</trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals('3d', $point->fix);
		$this->assertEquals(8, $point->satellitesNumber);
		$this->assertEquals(1.2, $point->hdop);
		$this->assertEquals(1.5, $point->vdop);
		$this->assertEquals(2.0, $point->pdop);
	}

	/**
	 * Test parsing point with missing optional elements.
	 * Requirements: 3.4
	 */
	public function test_parse_point_with_missing_optional_elements(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="40.0" lon="-74.0"></trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(40.0, $point->latitude);
		$this->assertEquals(-74.0, $point->longitude);
		$this->assertNull($point->elevation);
		$this->assertNull($point->time);
		$this->assertNull($point->name);
		$this->assertNull($point->comment);
		$this->assertNull($point->description);
		$this->assertNull($point->source);
		$this->assertNull($point->symbol);
		$this->assertNull($point->type);
		$this->assertNull($point->fix);
		$this->assertNull($point->satellitesNumber);
	}

	/**
	 * Test parsing point with only elevation (no time).
	 * Requirements: 3.4
	 */
	public function test_parse_point_with_elevation_only(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="51.5074" lon="-0.1278">
				<ele>11.0</ele>
			</trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(51.5074, $point->latitude);
		$this->assertEquals(-0.1278, $point->longitude);
		$this->assertEquals(11.0, $point->elevation);
		$this->assertNull($point->time);
	}

	/**
	 * Test parsing point with only time (no elevation).
	 * Requirements: 3.4
	 */
	public function test_parse_point_with_time_only(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="35.6762" lon="139.6503">
				<time>2023-06-15T09:00:00Z</time>
			</trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(35.6762, $point->latitude);
		$this->assertEquals(139.6503, $point->longitude);
		$this->assertNull($point->elevation);
		$this->assertInstanceOf(DateTime::class, $point->time);
	}

	/**
	 * Test parsing invalid XML with unknown element type.
	 * Requirements: 3.5
	 */
	public function test_parse_invalid_element_type_returns_null(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<invalidpt lat="50.0" lon="10.0"></invalidpt>
		');

		$point = PointParser::parse($xml);

		$this->assertNull($point);
	}

	/**
	 * Test parsing point with missing latitude attribute.
	 * Requirements: 3.5
	 */
	public function test_parse_point_with_missing_latitude(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lon="10.0"></trkpt>
		');

		$point = PointParser::parse($xml);

		// Parser should return null for invalid points (missing required latitude)
		$this->assertNull($point, "Parser should return null when latitude is missing");
	}

	/**
	 * Test parsing point with missing longitude attribute.
	 * Requirements: 3.5
	 */
	public function test_parse_point_with_missing_longitude(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="50.0"></trkpt>
		');

		$point = PointParser::parse($xml);

		// Parser should return null for invalid points (missing required longitude)
		$this->assertNull($point, "Parser should return null when longitude is missing");
	}

	/**
	 * Test parsing point with negative coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_point_with_negative_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<wpt lat="-33.8688" lon="-151.2093"></wpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(-33.8688, $point->latitude);
		$this->assertEquals(-151.2093, $point->longitude);
	}

	/**
	 * Test parsing point with zero coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_point_with_zero_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="0.0" lon="0.0"></trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(0.0, $point->latitude);
		$this->assertEquals(0.0, $point->longitude);
	}

	/**
	 * Test parsing point with extreme valid coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_point_with_extreme_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="90.0" lon="179.9"></trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(90.0, $point->latitude);
		$this->assertEquals(179.9, $point->longitude);
	}

	/**
	 * Test parsing point with negative elevation.
	 * Requirements: 3.2
	 */
	public function test_parse_point_with_negative_elevation(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<trkpt lat="31.7683" lon="35.2137">
				<ele>-430.5</ele>
			</trkpt>
		');

		$point = PointParser::parse($xml);

		$this->assertInstanceOf(Point::class, $point);
		$this->assertEquals(-430.5, $point->elevation);
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return PointParser::toXML($this->testModelInstance, $document);
	}
}
