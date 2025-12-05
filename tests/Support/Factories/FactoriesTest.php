<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Bounds;
use phpGPX\Models\Copyright;
use phpGPX\Models\Email;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use phpGPX\Tests\Support\TestCase;

/**
 * Test that all factories work correctly.
 */
final class FactoriesTest extends TestCase
{
	public function test_point_factory_creates_valid_point(): void
	{
		$point = PointFactory::create();
		
		$this->assertInstanceOf(Point::class, $point);
		$this->assertIsFloat($point->latitude);
		$this->assertIsFloat($point->longitude);
	}

	public function test_point_factory_creates_point_with_overrides(): void
	{
		$point = PointFactory::create([
			'latitude' => 50.0,
			'longitude' => 10.0,
			'elevation' => 100.0,
		]);
		
		$this->assertEquals(50.0, $point->latitude);
		$this->assertEquals(10.0, $point->longitude);
		$this->assertEquals(100.0, $point->elevation);
	}

	public function test_point_factory_creates_point_with_elevation(): void
	{
		$point = PointFactory::createWithElevation(123.45);
		
		$this->assertEquals(123.45, $point->elevation);
	}

	public function test_point_factory_creates_point_at_coordinates(): void
	{
		$point = PointFactory::createAtCoordinates(45.0, 15.0);
		
		$this->assertEquals(45.0, $point->latitude);
		$this->assertEquals(15.0, $point->longitude);
	}

	public function test_point_factory_creates_sequence(): void
	{
		$points = PointFactory::createSequence(5);
		
		$this->assertCount(5, $points);
		$this->assertContainsOnlyInstancesOf(Point::class, $points);
		
		// Verify points are in sequence
		$this->assertLessThan($points[1]->latitude, $points[0]->latitude);
		$this->assertLessThan($points[2]->latitude, $points[1]->latitude);
	}

	public function test_segment_factory_creates_valid_segment(): void
	{
		$segment = SegmentFactory::create();
		
		$this->assertInstanceOf(Segment::class, $segment);
		$this->assertIsArray($segment->points);
	}

	public function test_segment_factory_creates_segment_with_points(): void
	{
		$segment = SegmentFactory::createWithPoints(10);
		
		$this->assertCount(10, $segment->points);
		$this->assertContainsOnlyInstancesOf(Point::class, $segment->points);
	}

	public function test_track_factory_creates_valid_track(): void
	{
		$track = TrackFactory::create();
		
		$this->assertInstanceOf(Track::class, $track);
		$this->assertIsArray($track->segments);
	}

	public function test_track_factory_creates_track_with_points(): void
	{
		$track = TrackFactory::createWithPoints(15);
		
		$this->assertCount(1, $track->segments);
		$this->assertCount(15, $track->segments[0]->points);
	}

	public function test_track_factory_creates_track_with_segments(): void
	{
		$track = TrackFactory::createWithSegments(3, 5);
		
		$this->assertCount(3, $track->segments);
		
		foreach ($track->segments as $segment) {
			$this->assertCount(5, $segment->points);
		}
	}

	public function test_metadata_factory_creates_valid_metadata(): void
	{
		$metadata = MetadataFactory::create();
		
		$this->assertInstanceOf(Metadata::class, $metadata);
		$this->assertIsString($metadata->name);
		$this->assertInstanceOf(\DateTime::class, $metadata->time);
	}

	public function test_metadata_factory_creates_metadata_with_author(): void
	{
		$metadata = MetadataFactory::createWithAuthor('Test Author');
		
		$this->assertNotNull($metadata->author);
		$this->assertEquals('Test Author', $metadata->author->name);
	}

	public function test_metadata_factory_creates_metadata_with_links(): void
	{
		$urls = ['https://example.com', 'https://test.com'];
		$metadata = MetadataFactory::createWithLinks($urls);
		
		$this->assertCount(2, $metadata->links);
		$this->assertEquals('https://example.com', $metadata->links[0]->href);
		$this->assertEquals('https://test.com', $metadata->links[1]->href);
	}

	public function test_point_factory_generates_valid_random_latitude(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$latitude = PointFactory::randomLatitude();
			
			$this->assertGreaterThanOrEqual(-90.0, $latitude);
			$this->assertLessThanOrEqual(90.0, $latitude);
		}
	}

	public function test_point_factory_generates_valid_random_longitude(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$longitude = PointFactory::randomLongitude();
			
			$this->assertGreaterThanOrEqual(-180.0, $longitude);
			$this->assertLessThan(180.0, $longitude);
		}
	}

	public function test_bounds_factory_creates_valid_bounds(): void
	{
		$bounds = BoundsFactory::create();
		
		$this->assertInstanceOf(Bounds::class, $bounds);
		$this->assertIsFloat($bounds->minLatitude);
		$this->assertIsFloat($bounds->minLongitude);
		$this->assertIsFloat($bounds->maxLatitude);
		$this->assertIsFloat($bounds->maxLongitude);
		
		// Verify logical consistency
		$this->assertLessThanOrEqual($bounds->maxLatitude, $bounds->minLatitude);
		$this->assertLessThanOrEqual($bounds->maxLongitude, $bounds->minLongitude);
	}

	public function test_bounds_factory_creates_bounds_with_overrides(): void
	{
		$bounds = BoundsFactory::create([
			'minLatitude' => 10.0,
			'minLongitude' => 20.0,
			'maxLatitude' => 30.0,
			'maxLongitude' => 40.0,
		]);
		
		$this->assertEquals(10.0, $bounds->minLatitude);
		$this->assertEquals(20.0, $bounds->minLongitude);
		$this->assertEquals(30.0, $bounds->maxLatitude);
		$this->assertEquals(40.0, $bounds->maxLongitude);
	}

	public function test_bounds_factory_creates_random_valid_bounds(): void
	{
		for ($i = 0; $i < 10; $i++) {
			$bounds = BoundsFactory::createRandom();
			
			$this->assertInstanceOf(Bounds::class, $bounds);
			
			// Verify coordinate ranges
			$this->assertGreaterThanOrEqual(-90.0, $bounds->minLatitude);
			$this->assertLessThanOrEqual(90.0, $bounds->minLatitude);
			$this->assertGreaterThanOrEqual(-90.0, $bounds->maxLatitude);
			$this->assertLessThanOrEqual(90.0, $bounds->maxLatitude);
			$this->assertGreaterThanOrEqual(-180.0, $bounds->minLongitude);
			$this->assertLessThan(180.0, $bounds->minLongitude);
			$this->assertGreaterThanOrEqual(-180.0, $bounds->maxLongitude);
			$this->assertLessThan(180.0, $bounds->maxLongitude);
			
			// Verify logical consistency
			$this->assertLessThanOrEqual($bounds->maxLatitude, $bounds->minLatitude);
			$this->assertLessThanOrEqual($bounds->maxLongitude, $bounds->minLongitude);
		}
	}

	public function test_link_factory_creates_valid_link(): void
	{
		$link = LinkFactory::create();
		
		$this->assertInstanceOf(Link::class, $link);
		$this->assertIsString($link->href);
		$this->assertNotEmpty($link->href);
	}

	public function test_link_factory_creates_link_with_text(): void
	{
		$link = LinkFactory::createWithText('Test Link');
		
		$this->assertEquals('Test Link', $link->text);
	}

	public function test_link_factory_creates_link_with_url(): void
	{
		$link = LinkFactory::createWithUrl('https://test.com');
		
		$this->assertEquals('https://test.com', $link->href);
	}

	public function test_copyright_factory_creates_valid_copyright(): void
	{
		$copyright = CopyrightFactory::create();
		
		$this->assertInstanceOf(Copyright::class, $copyright);
		$this->assertIsString($copyright->author);
		$this->assertNotEmpty($copyright->author);
	}

	public function test_copyright_factory_creates_copyright_with_year(): void
	{
		$copyright = CopyrightFactory::createWithYear('2024');
		
		$this->assertEquals('2024', $copyright->year);
	}

	public function test_copyright_factory_creates_copyright_with_license(): void
	{
		$copyright = CopyrightFactory::createWithLicense('https://license.com');
		
		$this->assertEquals('https://license.com', $copyright->license);
	}

	public function test_email_factory_creates_valid_email(): void
	{
		$email = EmailFactory::create();
		
		$this->assertInstanceOf(Email::class, $email);
		$this->assertIsString($email->id);
		$this->assertIsString($email->domain);
		$this->assertNotEmpty($email->id);
		$this->assertNotEmpty($email->domain);
	}

	public function test_email_factory_creates_email_with_address(): void
	{
		$email = EmailFactory::createWithAddress('user', 'test.com');
		
		$this->assertEquals('user', $email->id);
		$this->assertEquals('test.com', $email->domain);
	}

	public function test_gpx_file_factory_creates_valid_gpx_file(): void
	{
		$gpxFile = GpxFileFactory::create();
		
		$this->assertInstanceOf(GpxFile::class, $gpxFile);
		$this->assertIsString($gpxFile->creator);
		$this->assertNotEmpty($gpxFile->creator);
	}

	public function test_gpx_file_factory_creates_gpx_file_with_metadata(): void
	{
		$gpxFile = GpxFileFactory::createWithMetadata();
		
		$this->assertNotNull($gpxFile->metadata);
		$this->assertInstanceOf(Metadata::class, $gpxFile->metadata);
	}

	public function test_gpx_file_factory_creates_gpx_file_with_waypoints(): void
	{
		$gpxFile = GpxFileFactory::createWithWaypoints(5);
		
		$this->assertCount(5, $gpxFile->waypoints);
		$this->assertContainsOnlyInstancesOf(Point::class, $gpxFile->waypoints);
	}

	public function test_gpx_file_factory_creates_gpx_file_with_tracks(): void
	{
		$gpxFile = GpxFileFactory::createWithTracks(2);
		
		$this->assertCount(2, $gpxFile->tracks);
		$this->assertContainsOnlyInstancesOf(Track::class, $gpxFile->tracks);
	}
}
