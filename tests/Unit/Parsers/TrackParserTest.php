<?php

declare(strict_types=1);

/**
 * Unit tests for TrackParser
 */

namespace phpGPX\Tests\Unit\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Track;
use phpGPX\Parsers\TrackParser;
use phpGPX\phpGPX;

class TrackParserTest extends ParserTestBase
{
	protected $testModelClass = Track::class;

	protected $testParserClass = TrackParser::class;

	/**
	 * @var Track
	 */
	protected $testModelInstance;

	public static function createTestInstance(): Track
	{
		$track = new Track();
		$track->name = 'Test Track';
		$track->comment = 'Test comment';
		$track->description = 'Test description';
		$track->source = 'GPS Device';
		$track->number = 1;
		$track->type = 'hiking';

		// Add two segments
		$track->segments = [
			SegmentParserTest::createTestInstance(),
			SegmentParserTest::createTestInstance(),
		];

		$track->recalculateStats();

		return $track;
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->testModelInstance = self::createTestInstance();
	}

	public function testParse(): void
	{
		$tracks = TrackParser::parse($this->testXmlFile->trk);

		$this->assertNotEmpty($tracks);
		$this->assertIsArray($tracks);
		$this->assertCount(1, $tracks);

		$track = $tracks[0];
		$this->assertInstanceOf(Track::class, $track);
		$this->assertEquals($this->testModelInstance->name, $track->name);
		$this->assertEquals($this->testModelInstance->comment, $track->comment);
		$this->assertEquals($this->testModelInstance->description, $track->description);
		$this->assertEquals($this->testModelInstance->source, $track->source);
		$this->assertEquals($this->testModelInstance->number, $track->number);
		$this->assertEquals($this->testModelInstance->type, $track->type);

		// Check segments
		$this->assertNotEmpty($track->segments);
		$this->assertCount(2, $track->segments);
	}

	/**
	 * Test parsing track XML with multiple segments.
	 * Requirements: 3.2
	 */
	public function test_parse_track_with_multiple_segments(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Multi-segment Track</name>
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
					<trkseg>
						<trkpt lat="46.572088" lon="8.414911">
							<ele>2420</ele>
							<time>2017-08-13T07:11:56Z</time>
						</trkpt>
						<trkpt lat="46.572069" lon="8.414912">
							<ele>2422</ele>
							<time>2017-08-13T07:12:15Z</time>
						</trkpt>
					</trkseg>
					<trkseg>
						<trkpt lat="46.572054" lon="8.414888">
							<ele>2425</ele>
							<time>2017-08-13T07:12:18Z</time>
						</trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertIsArray($tracks);
		$this->assertCount(1, $tracks);

		$track = $tracks[0];
		$this->assertInstanceOf(Track::class, $track);
		$this->assertEquals('Multi-segment Track', $track->name);
		$this->assertCount(3, $track->segments);

		// Check first segment has 2 points
		$this->assertCount(2, $track->segments[0]->points);
		// Check second segment has 2 points
		$this->assertCount(2, $track->segments[1]->points);
		// Check third segment has 1 point
		$this->assertCount(1, $track->segments[2]->points);
	}

	/**
	 * Test parsing track with metadata fields.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_track_with_metadata(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Mountain Hike</name>
					<cmt>Great weather</cmt>
					<desc>A beautiful mountain hike</desc>
					<src>Garmin GPS</src>
					<number>5</number>
					<type>hiking</type>
					<trkseg>
						<trkpt lat="50.0" lon="10.0">
							<ele>1000</ele>
						</trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertEquals('Mountain Hike', $track->name);
		$this->assertEquals('Great weather', $track->comment);
		$this->assertEquals('A beautiful mountain hike', $track->description);
		$this->assertEquals('Garmin GPS', $track->source);
		$this->assertEquals(5, $track->number);
		$this->assertEquals('hiking', $track->type);
	}

	/**
	 * Test parsing track with links.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_track_with_links(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Track with Links</name>
					<link href="https://example.com/track1">
						<text>Track Info</text>
					</link>
					<link href="https://example.com/track2">
						<text>More Info</text>
					</link>
					<trkseg>
						<trkpt lat="50.0" lon="10.0"></trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertNotEmpty($track->links);
		$this->assertCount(2, $track->links);
		$this->assertEquals('https://example.com/track1', $track->links[0]->href);
		$this->assertEquals('Track Info', $track->links[0]->text);
		$this->assertEquals('https://example.com/track2', $track->links[1]->href);
		$this->assertEquals('More Info', $track->links[1]->text);
	}

	/**
	 * Test parsing track with extensions.
	 * Requirements: 3.3
	 */
	public function test_parse_track_with_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1">
				<trk>
					<name>Track with Extensions</name>
					<extensions>
						<gpxtpx:TrackExtension>
							<gpxtpx:DisplayColor>Red</gpxtpx:DisplayColor>
						</gpxtpx:TrackExtension>
					</extensions>
					<trkseg>
						<trkpt lat="50.0" lon="10.0"></trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertNotNull($track->extensions);
	}

	/**
	 * Test parsing track with no segments.
	 * Requirements: 3.4
	 */
	public function test_parse_track_with_no_segments(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Empty Track</name>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertEquals('Empty Track', $track->name);
		$this->assertEmpty($track->segments);
	}

	/**
	 * Test parsing track with missing optional fields.
	 * Requirements: 3.4
	 */
	public function test_parse_track_with_missing_optional_fields(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<trkseg>
						<trkpt lat="50.0" lon="10.0"></trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertNull($track->name);
		$this->assertNull($track->comment);
		$this->assertNull($track->description);
		$this->assertNull($track->source);
		$this->assertNull($track->number);
		$this->assertNull($track->type);
		$this->assertEmpty($track->links);
		$this->assertNull($track->extensions);
	}

	/**
	 * Test parsing multiple tracks.
	 * Requirements: 3.2
	 */
	public function test_parse_multiple_tracks(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Track 1</name>
					<trkseg>
						<trkpt lat="50.0" lon="10.0"></trkpt>
					</trkseg>
				</trk>
				<trk>
					<name>Track 2</name>
					<trkseg>
						<trkpt lat="51.0" lon="11.0"></trkpt>
					</trkseg>
				</trk>
				<trk>
					<name>Track 3</name>
					<trkseg>
						<trkpt lat="52.0" lon="12.0"></trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(3, $tracks);
		$this->assertEquals('Track 1', $tracks[0]->name);
		$this->assertEquals('Track 2', $tracks[1]->name);
		$this->assertEquals('Track 3', $tracks[2]->name);
	}

	/**
	 * Test parsing track with statistics calculation enabled.
	 * Requirements: 3.2
	 */
	public function test_parse_track_with_stats_calculation(): void
	{
		// Enable stats calculation
		$originalSetting = phpGPX::$CALCULATE_STATS;
		phpGPX::$CALCULATE_STATS = true;

		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Track with Stats</name>
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
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertNotNull($track->stats);
		$this->assertGreaterThan(0, $track->stats->distance);

		// Restore original setting
		phpGPX::$CALCULATE_STATS = $originalSetting;
	}

	/**
	 * Test parsing track with single segment.
	 * Requirements: 3.2
	 */
	public function test_parse_track_with_single_segment(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<trk>
					<name>Single Segment Track</name>
					<trkseg>
						<trkpt lat="50.0" lon="10.0"></trkpt>
						<trkpt lat="50.1" lon="10.1"></trkpt>
						<trkpt lat="50.2" lon="10.2"></trkpt>
					</trkseg>
				</trk>
			</gpx>
		');

		$tracks = TrackParser::parse($xml->trk);

		$this->assertCount(1, $tracks);
		$track = $tracks[0];

		$this->assertCount(1, $track->segments);
		$this->assertCount(3, $track->segments[0]->points);
	}

	/**
	 * Returns output of ::toXML method of tested parser.
	 * @depends testParse
	 * @param DOMDocument $document
	 * @return DOMElement
	 */
	protected function convertToXML(DOMDocument $document)
	{
		return TrackParser::toXML($this->testModelInstance, $document);
	}
}
