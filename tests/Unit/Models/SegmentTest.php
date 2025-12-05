<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Extensions;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Stats;
use phpGPX\Parsers\SegmentParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\SegmentFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Segment model.
 */
final class SegmentTest extends TestCase
{
	public function test_segment_can_be_created(): void
	{
		// Arrange & Act
		$segment = new Segment();
		
		// Assert
		$this->assertInstanceOf(Segment::class, $segment);
		$this->assertIsArray($segment->points);
		$this->assertEmpty($segment->points);
		$this->assertNull($segment->extensions);
		$this->assertNull($segment->stats);
	}

	public function test_segment_can_add_points(): void
	{
		// Arrange
		$segment = new Segment();
		$point1 = PointFactory::create();
		$point2 = PointFactory::create();
		
		// Act
		$segment->points[] = $point1;
		$segment->points[] = $point2;
		
		// Assert
		$this->assertCount(2, $segment->points);
		$this->assertSame($point1, $segment->points[0]);
		$this->assertSame($point2, $segment->points[1]);
	}

	public function test_get_points_returns_all_points(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		
		// Act
		$points = $segment->getPoints();
		
		// Assert
		$this->assertCount(5, $points);
		$this->assertContainsOnlyInstancesOf(Point::class, $points);
	}

	public function test_get_points_returns_empty_array_when_no_points(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Act
		$points = $segment->getPoints();
		
		// Assert
		$this->assertIsArray($points);
		$this->assertEmpty($points);
	}

	public function test_recalculate_stats_creates_stats_object(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $segment->stats);
	}

	public function test_recalculate_stats_calculates_distance(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(10);
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertIsFloat($segment->stats->distance);
		$this->assertGreaterThan(0, $segment->stats->distance);
	}

	public function test_recalculate_stats_with_empty_segment(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $segment->stats);
		$this->assertEquals(0.0, $segment->stats->distance);
	}

	public function test_recalculate_stats_with_single_point(): void
	{
		// Arrange
		$segment = new Segment();
		$segment->points[] = PointFactory::create();
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $segment->stats);
		$this->assertNotNull($segment->stats->startedAtCoords);
		$this->assertNotNull($segment->stats->finishedAtCoords);
		$this->assertEquals($segment->stats->startedAtCoords, $segment->stats->finishedAtCoords);
	}

	public function test_recalculate_stats_with_elevation_data(): void
	{
		// Arrange
		$segment = new Segment();
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'elevation' => 100.0 + ($i * 10.0), // Ascending elevation
			]);
			$segment->points[] = $point;
		}
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertNotNull($segment->stats->cumulativeElevationGain);
		$this->assertGreaterThan(0, $segment->stats->cumulativeElevationGain);
		$this->assertNotNull($segment->stats->minAltitude);
		$this->assertNotNull($segment->stats->maxAltitude);
		$this->assertGreaterThan($segment->stats->minAltitude, $segment->stats->maxAltitude);
	}

	public function test_recalculate_stats_with_time_data(): void
	{
		// Arrange
		$segment = new Segment();
		$baseTime = new \DateTime('2024-01-01 10:00:00');
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'time' => (clone $baseTime)->modify("+{$i} minutes"),
			]);
			$segment->points[] = $point;
		}
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $segment->stats->startedAt);
		$this->assertInstanceOf(\DateTime::class, $segment->stats->finishedAt);
		$this->assertNotNull($segment->stats->duration);
		$this->assertEquals(540, $segment->stats->duration); // 9 minutes = 540 seconds
		$this->assertNotNull($segment->stats->averageSpeed);
		$this->assertGreaterThan(0, $segment->stats->averageSpeed);
	}

	public function test_recalculate_stats_calculates_average_pace(): void
	{
		// Arrange
		$segment = new Segment();
		$baseTime = new \DateTime('2024-01-01 10:00:00');
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'time' => (clone $baseTime)->modify("+{$i} minutes"),
			]);
			$segment->points[] = $point;
		}
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertNotNull($segment->stats->averagePace);
		$this->assertGreaterThan(0, $segment->stats->averagePace);
	}

	public function test_recalculate_stats_calculates_bounds(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(10);
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertNotNull($segment->stats->bounds);
		$this->assertIsArray($segment->stats->bounds);
		$this->assertCount(2, $segment->stats->bounds);
	}

	public function test_recalculate_stats_finds_min_and_max_altitude(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Add points with varying elevations
		$segment->points[] = PointFactory::create(['elevation' => 150.0]);
		$segment->points[] = PointFactory::create(['elevation' => 200.0]); // Max
		$segment->points[] = PointFactory::create(['elevation' => 100.0]); // Min
		$segment->points[] = PointFactory::create(['elevation' => 175.0]);
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertEquals(100.0, $segment->stats->minAltitude);
		$this->assertEquals(200.0, $segment->stats->maxAltitude);
		$this->assertNotNull($segment->stats->minAltitudeCoords);
		$this->assertNotNull($segment->stats->maxAltitudeCoords);
	}

	public function test_recalculate_stats_calculates_elevation_gain_and_loss(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Create a profile with ups and downs
		$segment->points[] = PointFactory::create(['elevation' => 100.0]);
		$segment->points[] = PointFactory::create(['elevation' => 150.0]); // +50
		$segment->points[] = PointFactory::create(['elevation' => 120.0]); // -30
		$segment->points[] = PointFactory::create(['elevation' => 180.0]); // +60
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertNotNull($segment->stats->cumulativeElevationGain);
		$this->assertNotNull($segment->stats->cumulativeElevationLoss);
		$this->assertGreaterThan(0, $segment->stats->cumulativeElevationGain);
		$this->assertGreaterThan(0, $segment->stats->cumulativeElevationLoss);
	}

	public function test_to_array_serialization(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(3);
		
		// Act
		$array = $segment->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('points', $array);
		$this->assertArrayHasKey('extensions', $array);
		$this->assertArrayHasKey('stats', $array);
	}

	public function test_to_array_includes_points(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		
		// Act
		$array = $segment->toArray();
		
		// Assert
		$this->assertIsArray($array['points']);
		$this->assertCount(5, $array['points']);
	}

	public function test_to_array_includes_stats_after_calculation(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		$segment->recalculateStats();
		
		// Act
		$array = $segment->toArray();
		
		// Assert
		$this->assertIsArray($array['stats']);
		$this->assertArrayHasKey('distance', $array['stats']);
	}

	public function test_to_array_with_empty_segment(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Act
		$array = $segment->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEmpty($array['points']);
		$this->assertNull($array['extensions']);
		$this->assertNull($array['stats']);
	}

	public function test_to_xml_serialization(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(3);
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = SegmentParser::toXML($segment, $document);
		
		// Assert
		$this->assertInstanceOf(\DOMElement::class, $xmlNode);
		$this->assertEquals('trkseg', $xmlNode->nodeName);
	}

	public function test_to_xml_includes_points(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = SegmentParser::toXML($segment, $document);
		
		// Assert
		$pointNodes = $xmlNode->getElementsByTagName('trkpt');
		$this->assertEquals(5, $pointNodes->length);
	}

	public function test_to_xml_with_empty_segment(): void
	{
		// Arrange
		$segment = new Segment();
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = SegmentParser::toXML($segment, $document);
		
		// Assert
		$this->assertInstanceOf(\DOMElement::class, $xmlNode);
		$this->assertEquals('trkseg', $xmlNode->nodeName);
		$pointNodes = $xmlNode->getElementsByTagName('trkpt');
		$this->assertEquals(0, $pointNodes->length);
	}

	public function test_to_xml_includes_extensions(): void
	{
		// Arrange
		$segment = new Segment();
		$segment->points[] = PointFactory::create();
		$segment->extensions = new Extensions();
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = SegmentParser::toXML($segment, $document);
		
		// Assert
		$extensionNodes = $xmlNode->getElementsByTagName('extensions');
		$this->assertEquals(1, $extensionNodes->length);
	}

	public function test_segment_can_store_extensions(): void
	{
		// Arrange
		$segment = new Segment();
		$extensions = new Extensions();
		
		// Act
		$segment->extensions = $extensions;
		
		// Assert
		$this->assertInstanceOf(Extensions::class, $segment->extensions);
		$this->assertSame($extensions, $segment->extensions);
	}

	public function test_recalculate_stats_with_null_elevation_values(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Add points with some null elevations
		$segment->points[] = PointFactory::create(['elevation' => 100.0]);
		$segment->points[] = PointFactory::create(['elevation' => null]);
		$segment->points[] = PointFactory::create(['elevation' => 150.0]);
		
		// Act
		$segment->recalculateStats();
		
		// Assert - should handle null elevations gracefully
		$this->assertInstanceOf(Stats::class, $segment->stats);
	}

	public function test_recalculate_stats_with_null_time_values(): void
	{
		// Arrange
		$segment = new Segment();
		
		// Add points with some null times
		$segment->points[] = PointFactory::create(['time' => new \DateTime('2024-01-01 10:00:00')]);
		$segment->points[] = PointFactory::create(['time' => null]);
		$segment->points[] = PointFactory::create(['time' => new \DateTime('2024-01-01 10:10:00')]);
		
		// Act
		$segment->recalculateStats();
		
		// Assert - should handle null times gracefully
		$this->assertInstanceOf(Stats::class, $segment->stats);
	}

	public function test_recalculate_stats_calculates_real_distance(): void
	{
		// Arrange
		$segment = new Segment();
		
		for ($i = 0; $i < 5; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'elevation' => 100.0 + ($i * 10.0),
			]);
			$segment->points[] = $point;
		}
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertNotNull($segment->stats->realDistance);
		$this->assertGreaterThan(0, $segment->stats->realDistance);
		// Real distance should be >= raw distance due to elevation changes
		$this->assertGreaterThanOrEqual($segment->stats->distance, $segment->stats->realDistance);
	}

	public function test_recalculate_stats_sets_start_and_finish_coordinates(): void
	{
		// Arrange
		$segment = new Segment();
		$segment->points[] = PointFactory::create(['latitude' => 54.0, 'longitude' => 9.0]);
		$segment->points[] = PointFactory::create(['latitude' => 54.1, 'longitude' => 9.1]);
		$segment->points[] = PointFactory::create(['latitude' => 54.2, 'longitude' => 9.2]);
		
		// Act
		$segment->recalculateStats();
		
		// Assert
		$this->assertIsArray($segment->stats->startedAtCoords);
		$this->assertIsArray($segment->stats->finishedAtCoords);
		$this->assertEquals(['lat' => 54.0, 'lng' => 9.0], $segment->stats->startedAtCoords);
		$this->assertEquals(['lat' => 54.2, 'lng' => 9.2], $segment->stats->finishedAtCoords);
	}

	public function test_recalculate_stats_resets_previous_stats(): void
	{
		// Arrange
		$segment = SegmentFactory::createWithPoints(5);
		$segment->recalculateStats();
		$firstDistance = $segment->stats->distance;
		
		// Add more points
		$segment->points[] = PointFactory::create(['latitude' => 55.0, 'longitude' => 10.0]);
		$segment->points[] = PointFactory::create(['latitude' => 55.1, 'longitude' => 10.1]);
		
		// Act
		$segment->recalculateStats();
		
		// Assert - distance should be recalculated and different
		$this->assertNotEquals($firstDistance, $segment->stats->distance);
		$this->assertGreaterThan($firstDistance, $segment->stats->distance);
	}
}

