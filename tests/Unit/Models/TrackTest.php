<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Link;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Stats;
use phpGPX\Models\Track;
use phpGPX\Parsers\TrackParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\SegmentFactory;
use phpGPX\Tests\Support\Factories\TrackFactory;
use phpGPX\Tests\Support\TestCase;
use Eris\Generators;
use Eris\TestTrait;

/**
 * Unit tests for Track model.
 */
final class TrackTest extends TestCase
{
	use TestTrait;
	public function test_track_can_be_created(): void
	{
		// Arrange & Act
		$track = new Track();
		
		// Assert
		$this->assertInstanceOf(Track::class, $track);
		$this->assertIsArray($track->segments);
		$this->assertEmpty($track->segments);
		$this->assertIsArray($track->links);
		$this->assertEmpty($track->links);
	}

	public function test_track_stores_basic_properties(): void
	{
		// Arrange
		$track = new Track();
		
		// Act
		$track->name = 'Morning Run';
		$track->comment = 'Great weather';
		$track->description = 'A nice morning run through the park';
		$track->source = 'Garmin eTrex';
		$track->number = 42;
		$track->type = 'running';
		
		// Assert
		$this->assertEquals('Morning Run', $track->name);
		$this->assertEquals('Great weather', $track->comment);
		$this->assertEquals('A nice morning run through the park', $track->description);
		$this->assertEquals('Garmin eTrex', $track->source);
		$this->assertEquals(42, $track->number);
		$this->assertEquals('running', $track->type);
	}

	public function test_track_can_add_segments(): void
	{
		// Arrange
		$track = new Track();
		$segment1 = SegmentFactory::create();
		$segment2 = SegmentFactory::create();
		
		// Act
		$track->segments[] = $segment1;
		$track->segments[] = $segment2;
		
		// Assert
		$this->assertCount(2, $track->segments);
		$this->assertSame($segment1, $track->segments[0]);
		$this->assertSame($segment2, $track->segments[1]);
	}

	public function test_track_can_add_links(): void
	{
		// Arrange
		$track = new Track();
		$link = new Link('https://example.com', 'Example Link');
		
		// Act
		$track->links[] = $link;
		
		// Assert
		$this->assertCount(1, $track->links);
		$this->assertEquals('https://example.com', $track->links[0]->href);
		$this->assertEquals('Example Link', $track->links[0]->text);
	}

	public function test_get_points_returns_all_points_from_all_segments(): void
	{
		// Arrange
		$track = TrackFactory::createWithSegments(3, 5);
		
		// Act
		$points = $track->getPoints();
		
		// Assert
		$this->assertCount(15, $points); // 3 segments * 5 points each
		$this->assertContainsOnlyInstancesOf(Point::class, $points);
	}

	public function test_get_points_returns_empty_array_when_no_segments(): void
	{
		// Arrange
		$track = new Track();
		
		// Act
		$points = $track->getPoints();
		
		// Assert
		$this->assertIsArray($points);
		$this->assertEmpty($points);
	}

	public function test_get_points_returns_empty_array_when_segments_have_no_points(): void
	{
		// Arrange
		$track = new Track();
		$track->segments[] = new Segment();
		$track->segments[] = new Segment();
		
		// Act
		$points = $track->getPoints();
		
		// Assert
		$this->assertIsArray($points);
		$this->assertEmpty($points);
	}

	public function test_recalculate_stats_creates_stats_object(): void
	{
		// Arrange
		$track = TrackFactory::createWithPoints(10);
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $track->stats);
	}

	public function test_recalculate_stats_calculates_distance(): void
	{
		// Arrange
		$track = TrackFactory::createWithPoints(10);
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertIsFloat($track->stats->distance);
		$this->assertGreaterThan(0, $track->stats->distance);
	}

	public function test_recalculate_stats_with_empty_track(): void
	{
		// Arrange
		$track = new Track();
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $track->stats);
		$this->assertEquals(0.0, $track->stats->distance);
	}

	public function test_recalculate_stats_with_empty_segments(): void
	{
		// Arrange
		$track = new Track();
		$track->segments[] = new Segment();
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $track->stats);
		$this->assertEquals(0.0, $track->stats->distance);
	}

	public function test_recalculate_stats_aggregates_multiple_segments(): void
	{
		// Arrange
		$track = TrackFactory::createWithSegments(3, 5);
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $track->stats);
		$this->assertGreaterThan(0, $track->stats->distance);
		
		// Calculate expected distance by summing segment distances
		$expectedDistance = 0;
		foreach ($track->segments as $segment) {
			$expectedDistance += $segment->stats->distance;
		}
		
		$this->assertEqualsWithDelta($expectedDistance, $track->stats->distance, 0.01);
	}

	public function test_recalculate_stats_with_elevation_data(): void
	{
		// Arrange
		$track = new Track();
		$segment = new Segment();
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'elevation' => 100.0 + ($i * 10.0), // Ascending elevation
			]);
			$segment->points[] = $point;
		}
		
		$track->segments[] = $segment;
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertNotNull($track->stats->cumulativeElevationGain);
		$this->assertGreaterThan(0, $track->stats->cumulativeElevationGain);
		$this->assertNotNull($track->stats->minAltitude);
		$this->assertNotNull($track->stats->maxAltitude);
		$this->assertGreaterThan($track->stats->minAltitude, $track->stats->maxAltitude);
	}

	public function test_recalculate_stats_with_time_data(): void
	{
		// Arrange
		$track = new Track();
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
		
		$track->segments[] = $segment;
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $track->stats->startedAt);
		$this->assertInstanceOf(\DateTime::class, $track->stats->finishedAt);
		$this->assertNotNull($track->stats->duration);
		$this->assertEquals(540, $track->stats->duration); // 9 minutes = 540 seconds
		$this->assertNotNull($track->stats->averageSpeed);
		$this->assertGreaterThan(0, $track->stats->averageSpeed);
	}

	public function test_to_array_serialization(): void
	{
		// Arrange
		$track = TrackFactory::create([
			'name' => 'Test Track',
			'description' => 'Test Description',
		]);
		$track->number = 1;
		$track->type = 'hiking';
		
		// Act
		$array = $track->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('name', $array);
		$this->assertArrayHasKey('desc', $array);
		$this->assertArrayHasKey('number', $array);
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('trkseg', $array);
		$this->assertArrayHasKey('stats', $array);
		$this->assertEquals('Test Track', $array['name']);
		$this->assertEquals('Test Description', $array['desc']);
		$this->assertEquals(1, $array['number']);
		$this->assertEquals('hiking', $array['type']);
	}

	public function test_to_array_includes_segments(): void
	{
		// Arrange
		$track = TrackFactory::createWithSegments(2, 3);
		
		// Act
		$array = $track->toArray();
		
		// Assert
		$this->assertArrayHasKey('trkseg', $array);
		$this->assertIsArray($array['trkseg']);
		$this->assertCount(2, $array['trkseg']);
	}

	public function test_to_array_includes_stats_after_calculation(): void
	{
		// Arrange
		$track = TrackFactory::createWithPoints(5);
		$track->recalculateStats();
		
		// Act
		$array = $track->toArray();
		
		// Assert
		$this->assertArrayHasKey('stats', $array);
		$this->assertIsArray($array['stats']);
		$this->assertArrayHasKey('distance', $array['stats']);
	}

	public function test_to_xml_serialization(): void
	{
		// Arrange
		$track = TrackFactory::create([
			'name' => 'XML Test Track',
		]);
		$track->segments[] = SegmentFactory::createWithPoints(3);
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = TrackParser::toXML($track, $document);
		
		// Assert
		$this->assertInstanceOf(\DOMElement::class, $xmlNode);
		$this->assertEquals('trk', $xmlNode->nodeName);
		
		// Check for name element
		$nameNodes = $xmlNode->getElementsByTagName('name');
		$this->assertGreaterThan(0, $nameNodes->length);
		$this->assertEquals('XML Test Track', $nameNodes->item(0)->nodeValue);
		
		// Check for segment elements
		$segmentNodes = $xmlNode->getElementsByTagName('trkseg');
		$this->assertEquals(1, $segmentNodes->length);
	}

	public function test_to_xml_with_multiple_segments(): void
	{
		// Arrange
		$track = TrackFactory::createWithSegments(3, 2);
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = TrackParser::toXML($track, $document);
		
		// Assert
		$segmentNodes = $xmlNode->getElementsByTagName('trkseg');
		$this->assertEquals(3, $segmentNodes->length);
	}

	public function test_to_xml_includes_all_properties(): void
	{
		// Arrange
		$track = TrackFactory::create([
			'name' => 'Complete Track',
			'description' => 'Full description',
		]);
		$track->comment = 'Test comment';
		$track->source = 'Test source';
		$track->number = 99;
		$track->type = 'cycling';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = TrackParser::toXML($track, $document);
		
		// Assert
		$this->assertEquals('Complete Track', $xmlNode->getElementsByTagName('name')->item(0)->nodeValue);
		$this->assertEquals('Full description', $xmlNode->getElementsByTagName('desc')->item(0)->nodeValue);
		$this->assertEquals('Test comment', $xmlNode->getElementsByTagName('cmt')->item(0)->nodeValue);
		$this->assertEquals('Test source', $xmlNode->getElementsByTagName('src')->item(0)->nodeValue);
		$this->assertEquals('99', $xmlNode->getElementsByTagName('number')->item(0)->nodeValue);
		$this->assertEquals('cycling', $xmlNode->getElementsByTagName('type')->item(0)->nodeValue);
	}

	public function test_stats_bounds_are_calculated(): void
	{
		// Arrange
		$track = TrackFactory::createWithPoints(10);
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertNotNull($track->stats->bounds);
		$this->assertIsArray($track->stats->bounds);
		$this->assertCount(2, $track->stats->bounds);
	}

	public function test_track_with_single_point(): void
	{
		// Arrange
		$track = new Track();
		$segment = new Segment();
		$segment->points[] = PointFactory::create();
		$track->segments[] = $segment;
		
		// Act
		$track->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $track->stats);
		$this->assertNotNull($track->stats->startedAtCoords);
		$this->assertNotNull($track->stats->finishedAtCoords);
		$this->assertEquals($track->stats->startedAtCoords, $track->stats->finishedAtCoords);
	}

	public function test_get_points_sorts_by_timestamp_when_enabled(): void
	{
		// Arrange
		$originalSortSetting = \phpGPX\phpGPX::$SORT_BY_TIMESTAMP;
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = true;
		
		$track = new Track();
		$segment = new Segment();
		
		// Add points with timestamps in reverse order
		$time3 = new \DateTime('2024-01-01 12:00:00');
		$time2 = new \DateTime('2024-01-01 11:00:00');
		$time1 = new \DateTime('2024-01-01 10:00:00');
		
		$segment->points[] = PointFactory::create(['time' => $time3, 'latitude' => 54.3]);
		$segment->points[] = PointFactory::create(['time' => $time2, 'latitude' => 54.2]);
		$segment->points[] = PointFactory::create(['time' => $time1, 'latitude' => 54.1]);
		
		$track->segments[] = $segment;
		
		// Act
		$points = $track->getPoints();
		
		// Assert
		$this->assertEquals($time1, $points[0]->time);
		$this->assertEquals($time2, $points[1]->time);
		$this->assertEquals($time3, $points[2]->time);
		
		// Restore original setting
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = $originalSortSetting;
	}

	public function test_get_points_does_not_sort_when_sorting_disabled(): void
	{
		// Arrange
		$originalSortSetting = \phpGPX\phpGPX::$SORT_BY_TIMESTAMP;
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = false;
		
		$track = new Track();
		$segment = new Segment();
		
		// Add points with timestamps in reverse order
		$time3 = new \DateTime('2024-01-01 12:00:00');
		$time2 = new \DateTime('2024-01-01 11:00:00');
		$time1 = new \DateTime('2024-01-01 10:00:00');
		
		$segment->points[] = PointFactory::create(['time' => $time3, 'latitude' => 54.3]);
		$segment->points[] = PointFactory::create(['time' => $time2, 'latitude' => 54.2]);
		$segment->points[] = PointFactory::create(['time' => $time1, 'latitude' => 54.1]);
		
		$track->segments[] = $segment;
		
		// Act
		$points = $track->getPoints();
		
		// Assert - should remain in original order
		$this->assertEquals($time3, $points[0]->time);
		$this->assertEquals($time2, $points[1]->time);
		$this->assertEquals($time1, $points[2]->time);
		
		// Restore original setting
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = $originalSortSetting;
	}

	/**
	 * Property test: Statistics are non-negative.
	 * 
	 * **Feature: test-coverage, Property 6: Statistics are non-negative**
	 * 
	 * For any track with valid points, calculated statistics (distance, duration)
	 * should always be non-negative values. This is a fundamental invariant - 
	 * distances, durations, and elevation metrics cannot be negative by definition.
	 * 
	 * **Validates: Requirements 2.4**
	 */
	public function test_property_statistics_are_non_negative(): void
	{
		$this
			->withRand('mt_rand')  // REQUIRED: Initialize random generator
			->forAll(
				// Generate parameters for creating random tracks
				Generators::choose(1, 5),    // Number of segments
				Generators::choose(2, 10)    // Number of points per segment
			)
			->withMaxSize(100) // Run 100 iterations as specified in design
			->then(function ($numSegments, $numPoints) {
				// Create a track with random segments and points
				$track = new Track();
				
				$baseTime = new \DateTime('2024-01-01 10:00:00');
				$baseLatitude = 54.0;
				$baseLongitude = 9.0;
				$baseElevation = 100.0;
				
				for ($s = 0; $s < $numSegments; $s++) {
					$segment = new Segment();
					
					for ($p = 0; $p < $numPoints; $p++) {
						// Generate random but valid coordinates
						$latOffset = (mt_rand(-1000, 1000) / 10000.0); // ±0.1 degrees
						$lonOffset = (mt_rand(-1000, 1000) / 10000.0); // ±0.1 degrees
						$elevOffset = (mt_rand(-50, 50) / 1.0); // ±50 meters
						$timeOffset = $p * 60; // 1 minute between points
						
						$point = PointFactory::create([
							'latitude' => $baseLatitude + $latOffset,
							'longitude' => $baseLongitude + $lonOffset,
							'elevation' => $baseElevation + $elevOffset,
							'time' => (clone $baseTime)->modify("+{$timeOffset} seconds"),
						]);
						
						$segment->points[] = $point;
					}
					
					$track->segments[] = $segment;
					
					// Update base values for next segment
					$baseLatitude += 0.01;
					$baseLongitude += 0.01;
					$baseTime = (clone $baseTime)->modify("+{$numPoints} minutes");
				}
				
				// Calculate statistics
				$track->recalculateStats();
				
				// Property: All statistics must be non-negative
				$this->assertGreaterThanOrEqual(
					0.0,
					$track->stats->distance,
					sprintf(
						"Distance must be non-negative, got: %.2f\n" .
						"Track has %d segments with %d points each",
						$track->stats->distance,
						$numSegments,
						$numPoints
					)
				);
				
				$this->assertGreaterThanOrEqual(
					0.0,
					$track->stats->realDistance,
					sprintf(
						"Real distance must be non-negative, got: %.2f\n" .
						"Track has %d segments with %d points each",
						$track->stats->realDistance,
						$numSegments,
						$numPoints
					)
				);
				
				// Duration should be non-negative if it's set
				if ($track->stats->duration !== null) {
					$this->assertGreaterThanOrEqual(
						0,
						$track->stats->duration,
						sprintf(
							"Duration must be non-negative, got: %d\n" .
							"Track has %d segments with %d points each",
							$track->stats->duration,
							$numSegments,
							$numPoints
						)
					);
				}
				
				// Average speed should be non-negative if it's set
				if ($track->stats->averageSpeed !== null) {
					$this->assertGreaterThanOrEqual(
						0.0,
						$track->stats->averageSpeed,
						sprintf(
							"Average speed must be non-negative, got: %.2f\n" .
							"Track has %d segments with %d points each",
							$track->stats->averageSpeed,
							$numSegments,
							$numPoints
						)
					);
				}
				
				// Average pace should be non-negative if it's set
				if ($track->stats->averagePace !== null) {
					$this->assertGreaterThanOrEqual(
						0.0,
						$track->stats->averagePace,
						sprintf(
							"Average pace must be non-negative, got: %.2f\n" .
							"Track has %d segments with %d points each",
							$track->stats->averagePace,
							$numSegments,
							$numPoints
						)
					);
				}
				
				// Elevation gain should be non-negative if it's set
				if ($track->stats->cumulativeElevationGain !== null) {
					$this->assertGreaterThanOrEqual(
						0.0,
						$track->stats->cumulativeElevationGain,
						sprintf(
							"Cumulative elevation gain must be non-negative, got: %.2f\n" .
							"Track has %d segments with %d points each",
							$track->stats->cumulativeElevationGain,
							$numSegments,
							$numPoints
						)
					);
				}
				
				// Elevation loss should be non-negative if it's set
				if ($track->stats->cumulativeElevationLoss !== null) {
					$this->assertGreaterThanOrEqual(
						0.0,
						$track->stats->cumulativeElevationLoss,
						sprintf(
							"Cumulative elevation loss must be non-negative, got: %.2f\n" .
							"Track has %d segments with %d points each",
							$track->stats->cumulativeElevationLoss,
							$numSegments,
							$numPoints
						)
					);
				}
			});
	}
}
