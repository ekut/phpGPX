<?php

declare(strict_types=1);
/**
 * @author miqwit
 */

namespace phpGPX\Tests\Unit\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Segment;
use phpGPX\Parsers\SegmentParser;
use phpGPX\phpGPX;

class SegmentParserTest extends AbstractParserTest
{
	protected $testModelClass = Segment::class;

	protected $testParserClass = SegmentParser::class;

	/**
	 * @var Segment
	 */
	protected $testModelInstance;

	public static function createTestInstance()
	{
		$segment = new Segment();
		$segment->points = [
			PointParserTest::createTestInstanceWithValues(46.571948, 8.414757, 2419, "2017-08-13T07:10:41.000Z"),
			PointParserTest::createTestInstanceWithValues(46.572016, 8.414866, 2418.8833883882, "2017-08-13T07:10:54.000Z"),
			PointParserTest::createTestInstanceWithValues(46.572088, 8.414911, 2419.8999900064, "2017-08-13T07:11:56.000Z"),
			PointParserTest::createTestInstanceWithValues(46.572069, 8.414912, 2422, "2017-08-13T07:12:15.000Z"),
			PointParserTest::createTestInstanceWithValues(46.572054, 8.414888, 2425, "2017-08-13T07:12:18.000Z"),
		];
		$segment->recalculateStats();

		return $segment;
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$segment = SegmentParser::parse($this->testXmlFile->trkseg);

		$this->assertNotEmpty($segment);

		// Test second point
		$point = $segment[0]->points[1];
		$this->assertEquals($this->testModelInstance->points[1]->latitude, $point->latitude);
		$this->assertEquals($this->testModelInstance->points[1]->longitude, $point->longitude);
		$this->assertEquals($this->testModelInstance->points[1]->elevation, $point->elevation);
		$this->assertEquals($this->testModelInstance->points[1]->time, $point->time);

		// Stats
		$this->assertNotEmpty($this->testModelInstance->stats);

		// Check the boundaries
		$nw = $this->testModelInstance->stats->bounds[0];
		$se = $this->testModelInstance->stats->bounds[1];
		$this->assertEquals(46.572088, $nw["lat"]);
		$this->assertEquals(8.414757, $nw["lng"]);
		$this->assertEquals(46.571948, $se["lat"]);
		$this->assertEquals(8.414912, $se["lng"]);
	}

	/**
	 * Test parsing segment XML with multiple points.
	 * Requirements: 3.2
	 */
	public function test_parse_segment_with_multiple_points(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="46.571948" lon="8.414757">
						<ele>2419</ele>
						<time>2017-08-13T07:10:41Z</time>
					</trkpt>
					<trkpt lat="46.572016" lon="8.414866">
						<ele>2418</ele>
						<time>2017-08-13T07:10:54Z</time>
					</trkpt>
					<trkpt lat="46.572088" lon="8.414911">
						<ele>2420</ele>
						<time>2017-08-13T07:11:56Z</time>
					</trkpt>
					<trkpt lat="46.572069" lon="8.414912">
						<ele>2422</ele>
						<time>2017-08-13T07:12:15Z</time>
					</trkpt>
					<trkpt lat="46.572054" lon="8.414888">
						<ele>2425</ele>
						<time>2017-08-13T07:12:18Z</time>
					</trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertIsArray($segments);
		$this->assertCount(1, $segments);

		$segment = $segments[0];
		$this->assertInstanceOf(\phpGPX\Models\Segment::class, $segment);
		$this->assertCount(5, $segment->points);

		// Verify first point
		$this->assertEquals(46.571948, $segment->points[0]->latitude);
		$this->assertEquals(8.414757, $segment->points[0]->longitude);
		$this->assertEquals(2419, $segment->points[0]->elevation);

		// Verify last point
		$this->assertEquals(46.572054, $segment->points[4]->latitude);
		$this->assertEquals(8.414888, $segment->points[4]->longitude);
		$this->assertEquals(2425, $segment->points[4]->elevation);
	}

	/**
	 * Test parsing segment with single point.
	 * Requirements: 3.2
	 */
	public function test_parse_segment_with_single_point(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="50.0" lon="10.0">
						<ele>1000</ele>
					</trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertCount(1, $segment->points);
		$this->assertEquals(50.0, $segment->points[0]->latitude);
		$this->assertEquals(10.0, $segment->points[0]->longitude);
	}

	/**
	 * Test parsing empty segment (no points).
	 * Requirements: 3.4
	 */
	public function test_parse_empty_segment(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg></trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		// Empty segments are skipped by the parser
		$this->assertEmpty($segments);
	}

	/**
	 * Test parsing segment with points without elevation.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_segment_with_points_without_elevation(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="50.0" lon="10.0"></trkpt>
					<trkpt lat="50.1" lon="10.1"></trkpt>
					<trkpt lat="50.2" lon="10.2"></trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertCount(3, $segment->points);
		$this->assertNull($segment->points[0]->elevation);
		$this->assertNull($segment->points[1]->elevation);
		$this->assertNull($segment->points[2]->elevation);
	}

	/**
	 * Test parsing segment with points without time.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_segment_with_points_without_time(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="50.0" lon="10.0">
						<ele>1000</ele>
					</trkpt>
					<trkpt lat="50.1" lon="10.1">
						<ele>1010</ele>
					</trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertCount(2, $segment->points);
		$this->assertNull($segment->points[0]->time);
		$this->assertNull($segment->points[1]->time);
	}

	/**
	 * Test parsing segment with extensions.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_segment_with_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1">
				<trkseg>
					<trkpt lat="50.0" lon="10.0"></trkpt>
					<extensions>
						<gpxtpx:SegmentExtension>
							<gpxtpx:Distance>1000</gpxtpx:Distance>
						</gpxtpx:SegmentExtension>
					</extensions>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertNotNull($segment->extensions);
	}

	/**
	 * Test parsing multiple segments.
	 * Requirements: 3.2
	 */
	public function test_parse_multiple_segments(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="50.0" lon="10.0"></trkpt>
					<trkpt lat="50.1" lon="10.1"></trkpt>
				</trkseg>
				<trkseg>
					<trkpt lat="51.0" lon="11.0"></trkpt>
					<trkpt lat="51.1" lon="11.1"></trkpt>
				</trkseg>
				<trkseg>
					<trkpt lat="52.0" lon="12.0"></trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(3, $segments);
		$this->assertCount(2, $segments[0]->points);
		$this->assertCount(2, $segments[1]->points);
		$this->assertCount(1, $segments[2]->points);
	}

	/**
	 * Test parsing segment with statistics calculation enabled.
	 * Requirements: 3.2
	 */
	public function test_parse_segment_with_stats_calculation(): void
	{
		// Enable stats calculation
		$originalSetting = phpGPX::$CALCULATE_STATS;
		phpGPX::$CALCULATE_STATS = true;

		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="46.571948" lon="8.414757">
						<ele>2419</ele>
						<time>2017-08-13T07:10:41Z</time>
					</trkpt>
					<trkpt lat="46.572016" lon="8.414866">
						<ele>2418</ele>
						<time>2017-08-13T07:10:54Z</time>
					</trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertNotNull($segment->stats);
		$this->assertGreaterThan(0, $segment->stats->distance);

		// Restore original setting
		phpGPX::$CALCULATE_STATS = $originalSetting;
	}

	/**
	 * Test parsing segment with points in different hemispheres.
	 * Requirements: 3.2
	 */
	public function test_parse_segment_with_points_in_different_hemispheres(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trkseg>
					<trkpt lat="50.0" lon="10.0"></trkpt>
					<trkpt lat="-50.0" lon="-10.0"></trkpt>
				</trkseg>
			</gpx>
		');

		$segments = SegmentParser::parse($xml->trkseg);

		$this->assertCount(1, $segments);
		$segment = $segments[0];

		$this->assertCount(2, $segment->points);
		$this->assertEquals(50.0, $segment->points[0]->latitude);
		$this->assertEquals(10.0, $segment->points[0]->longitude);
		$this->assertEquals(-50.0, $segment->points[1]->latitude);
		$this->assertEquals(-10.0, $segment->points[1]->longitude);
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return SegmentParser::toXML($this->testModelInstance, $document);
	}
}
