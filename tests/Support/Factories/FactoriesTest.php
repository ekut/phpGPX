<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Point;
use phpGPX\Models\Track;
use phpGPX\Models\Segment;
use phpGPX\Models\Metadata;
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
}
