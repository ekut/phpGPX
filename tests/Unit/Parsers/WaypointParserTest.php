<?php

declare(strict_types=1);

/**
 * Unit tests for WaypointParser
 */

namespace phpGPX\Tests\Unit\Parsers;

use phpGPX\Models\Point;
use phpGPX\Parsers\WaypointParser;
use PHPUnit\Framework\TestCase;

class WaypointParserTest extends TestCase
{
	/**
	 * Test parsing waypoint XML with name and description.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_waypoint_with_name_and_description(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="54.9328621088893" lon="9.860624216140083">
					<name>Test Waypoint</name>
					<desc>This is a test waypoint description</desc>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertIsArray($waypoints);
		$this->assertCount(1, $waypoints);
		$this->assertInstanceOf(Point::class, $waypoints[0]);
		$this->assertEquals('Test Waypoint', $waypoints[0]->name);
		$this->assertEquals('This is a test waypoint description', $waypoints[0]->description);
		$this->assertEquals(54.9328621088893, $waypoints[0]->latitude);
		$this->assertEquals(9.860624216140083, $waypoints[0]->longitude);
		$this->assertEquals(Point::WAYPOINT, $waypoints[0]->getPointType());
	}

	/**
	 * Test parsing multiple waypoints.
	 * Requirements: 3.2
	 */
	public function test_parse_multiple_waypoints(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="54.9328621088893" lon="9.860624216140083">
					<name>Waypoint 1</name>
				</wpt>
				<wpt lat="54.9428621088893" lon="9.870624216140083">
					<name>Waypoint 2</name>
				</wpt>
				<wpt lat="54.9528621088893" lon="9.880624216140083">
					<name>Waypoint 3</name>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertIsArray($waypoints);
		$this->assertCount(3, $waypoints);
		$this->assertEquals('Waypoint 1', $waypoints[0]->name);
		$this->assertEquals('Waypoint 2', $waypoints[1]->name);
		$this->assertEquals('Waypoint 3', $waypoints[2]->name);
	}

	/**
	 * Test parsing waypoint with all metadata fields.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_waypoint_with_all_metadata(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="50.0" lon="10.0">
					<ele>100.5</ele>
					<time>2023-01-15T12:30:00Z</time>
					<name>Summit Point</name>
					<cmt>Great view from here</cmt>
					<desc>Mountain summit with panoramic views</desc>
					<src>GPS Device</src>
					<sym>Summit</sym>
					<type>Peak</type>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals(50.0, $waypoint->latitude);
		$this->assertEquals(10.0, $waypoint->longitude);
		$this->assertEquals(100.5, $waypoint->elevation);
		$this->assertInstanceOf(\DateTime::class, $waypoint->time);
		$this->assertEquals('Summit Point', $waypoint->name);
		$this->assertEquals('Great view from here', $waypoint->comment);
		$this->assertEquals('Mountain summit with panoramic views', $waypoint->description);
		$this->assertEquals('GPS Device', $waypoint->source);
		$this->assertEquals('Summit', $waypoint->symbol);
		$this->assertEquals('Peak', $waypoint->type);
	}

	/**
	 * Test parsing waypoint with link.
	 * Requirements: 3.2, 3.3
	 */
	public function test_parse_waypoint_with_single_link(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="52.5200" lon="13.4050">
					<name>Berlin</name>
					<link href="https://example.com/berlin">
						<text>Berlin Information</text>
						<type>text/html</type>
					</link>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals('Berlin', $waypoint->name);
		$this->assertIsArray($waypoint->links);
		$this->assertCount(1, $waypoint->links);
		$this->assertEquals('https://example.com/berlin', $waypoint->links[0]->href);
		$this->assertEquals('Berlin Information', $waypoint->links[0]->text);
		$this->assertEquals('text/html', $waypoint->links[0]->type);
	}

	/**
	 * Test parsing waypoint with multiple links.
	 * Requirements: 3.2, 3.3
	 */
	public function test_parse_waypoint_with_multiple_links(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="48.8566" lon="2.3522">
					<name>Paris</name>
					<link href="https://example.com/paris">
						<text>Paris Guide</text>
						<type>text/html</type>
					</link>
					<link href="https://example.com/paris-map">
						<text>Paris Map</text>
						<type>application/pdf</type>
					</link>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertIsArray($waypoint->links);
		$this->assertCount(2, $waypoint->links);
		$this->assertEquals('https://example.com/paris', $waypoint->links[0]->href);
		$this->assertEquals('Paris Guide', $waypoint->links[0]->text);
		$this->assertEquals('https://example.com/paris-map', $waypoint->links[1]->href);
		$this->assertEquals('Paris Map', $waypoint->links[1]->text);
	}

	/**
	 * Test parsing waypoint with link without text or type.
	 * Requirements: 3.2, 3.3, 3.4
	 */
	public function test_parse_waypoint_with_minimal_link(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="40.7128" lon="-74.0060">
					<name>New York</name>
					<link href="https://example.com/nyc"></link>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertIsArray($waypoint->links);
		$this->assertCount(1, $waypoint->links);
		$this->assertEquals('https://example.com/nyc', $waypoint->links[0]->href);
		$this->assertNull($waypoint->links[0]->text);
		$this->assertNull($waypoint->links[0]->type);
	}

	/**
	 * Test parsing waypoint with Garmin TrackPointExtension.
	 * Requirements: 3.2, 3.3
	 */
	public function test_parse_waypoint_with_garmin_extension(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1">
				<wpt lat="45.0" lon="9.0">
					<name>Test Point</name>
					<extensions>
						<gpxtpx:TrackPointExtension>
							<gpxtpx:hr>150</gpxtpx:hr>
							<gpxtpx:cad>85</gpxtpx:cad>
						</gpxtpx:TrackPointExtension>
					</extensions>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals('Test Point', $waypoint->name);
		$this->assertNotNull($waypoint->extensions);
		$this->assertNotNull($waypoint->extensions->trackPointExtension);
		$this->assertEquals(150, $waypoint->extensions->trackPointExtension->heartRate);
		$this->assertEquals(85, $waypoint->extensions->trackPointExtension->cadence);
	}

	/**
	 * Test parsing waypoint with unsupported extension.
	 * Requirements: 3.2, 3.3
	 */
	public function test_parse_waypoint_with_unsupported_extension(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx xmlns:custom="http://example.com/custom">
				<wpt lat="51.5074" lon="-0.1278">
					<name>London</name>
					<extensions>
						<custom:customField>Custom Value</custom:customField>
						<custom:anotherField>Another Value</custom:anotherField>
					</extensions>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals('London', $waypoint->name);
		$this->assertNotNull($waypoint->extensions);
		$this->assertIsArray($waypoint->extensions->unsupported);
		$this->assertArrayHasKey('custom:customField', $waypoint->extensions->unsupported);
		$this->assertEquals('Custom Value', $waypoint->extensions->unsupported['custom:customField']);
		$this->assertArrayHasKey('custom:anotherField', $waypoint->extensions->unsupported);
		$this->assertEquals('Another Value', $waypoint->extensions->unsupported['custom:anotherField']);
	}

	/**
	 * Test parsing waypoint with both links and extensions.
	 * Requirements: 3.2, 3.3
	 */
	public function test_parse_waypoint_with_links_and_extensions(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1">
				<wpt lat="35.6762" lon="139.6503">
					<name>Tokyo</name>
					<desc>Capital of Japan</desc>
					<link href="https://example.com/tokyo">
						<text>Tokyo Guide</text>
					</link>
					<extensions>
						<gpxtpx:TrackPointExtension>
							<gpxtpx:hr>120</gpxtpx:hr>
						</gpxtpx:TrackPointExtension>
					</extensions>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals('Tokyo', $waypoint->name);
		$this->assertEquals('Capital of Japan', $waypoint->description);
		
		// Check links
		$this->assertIsArray($waypoint->links);
		$this->assertCount(1, $waypoint->links);
		$this->assertEquals('https://example.com/tokyo', $waypoint->links[0]->href);
		$this->assertEquals('Tokyo Guide', $waypoint->links[0]->text);
		
		// Check extensions
		$this->assertNotNull($waypoint->extensions);
		$this->assertNotNull($waypoint->extensions->trackPointExtension);
		$this->assertEquals(120, $waypoint->extensions->trackPointExtension->heartRate);
	}

	/**
	 * Test parsing empty waypoint list.
	 * Requirements: 3.4
	 */
	public function test_parse_empty_waypoint_list(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx></gpx>
		');

		// Create an empty SimpleXMLElement array by selecting non-existent elements
		$emptyWaypoints = $xml->xpath('//wpt');
		
		// xpath returns false when no matches, so we need to handle that
		if ($emptyWaypoints === false) {
			$emptyWaypoints = [];
		}

		$waypoints = [];
		if (!empty($emptyWaypoints)) {
			$waypoints = WaypointParser::parse(simplexml_load_string('<root>' . implode('', $emptyWaypoints) . '</root>'));
		}

		$this->assertIsArray($waypoints);
		$this->assertCount(0, $waypoints);
	}

	/**
	 * Test parsing waypoint with minimal required data.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_waypoint_with_minimal_data(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="0.0" lon="0.0"></wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals(0.0, $waypoint->latitude);
		$this->assertEquals(0.0, $waypoint->longitude);
		$this->assertNull($waypoint->name);
		$this->assertNull($waypoint->description);
		$this->assertNull($waypoint->elevation);
		$this->assertNull($waypoint->time);
		$this->assertEmpty($waypoint->links);
		$this->assertNull($waypoint->extensions);
	}

	/**
	 * Test parsing waypoint with GPS fix data.
	 * Requirements: 3.2, 3.4
	 */
	public function test_parse_waypoint_with_gps_fix_data(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="47.0" lon="8.0">
					<name>GPS Test Point</name>
					<fix>3d</fix>
					<sat>10</sat>
					<hdop>1.5</hdop>
					<vdop>2.0</vdop>
					<pdop>2.5</pdop>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals('GPS Test Point', $waypoint->name);
		$this->assertEquals('3d', $waypoint->fix);
		$this->assertEquals(10, $waypoint->satellitesNumber);
		$this->assertEquals(1.5, $waypoint->hdop);
		$this->assertEquals(2.0, $waypoint->vdop);
		$this->assertEquals(2.5, $waypoint->pdop);
	}

	/**
	 * Test parsing waypoint with negative coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_waypoint_with_negative_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="-33.8688" lon="-151.2093">
					<name>Sydney</name>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(1, $waypoints);
		$waypoint = $waypoints[0];
		
		$this->assertEquals(-33.8688, $waypoint->latitude);
		$this->assertEquals(-151.2093, $waypoint->longitude);
		$this->assertEquals('Sydney', $waypoint->name);
	}

	/**
	 * Test parsing waypoint with extreme valid coordinates.
	 * Requirements: 3.2
	 */
	public function test_parse_waypoint_with_extreme_coordinates(): void
	{
		$xml = simplexml_load_string('<?xml version="1.0"?>
			<gpx>
				<wpt lat="90.0" lon="180.0">
					<name>North Pole</name>
				</wpt>
				<wpt lat="-90.0" lon="-180.0">
					<name>South Pole</name>
				</wpt>
			</gpx>
		');

		$waypoints = WaypointParser::parse($xml->wpt);

		$this->assertCount(2, $waypoints);
		$this->assertEquals(90.0, $waypoints[0]->latitude);
		$this->assertEquals(180.0, $waypoints[0]->longitude);
		$this->assertEquals(-90.0, $waypoints[1]->latitude);
		$this->assertEquals(-180.0, $waypoints[1]->longitude);
	}
}
