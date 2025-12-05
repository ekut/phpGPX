<?php

declare(strict_types=1);

namespace phpGPX\Tests\Integration;

use phpGPX\phpGPX;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use phpGPX\Tests\Support\TestCase;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\MetadataFactory;

/**
 * Integration tests for end-to-end statistics calculation.
 * 
 * Tests Requirements 4.2
 */
final class StatsCalculationTest extends TestCase
{
	/**
	 * Test loading GPX file and calculating complete statistics.
	 * 
	 * Requirements: 4.2
	 */
	public function test_load_gpx_file_and_calculate_complete_statistics(): void
	{
		// Arrange
		$gpx = new phpGPX();
		phpGPX::$CALCULATE_STATS = true;
		$filePath = $this->getFixturePath('minimal-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert - File loaded
		$this->assertInstanceOf(GpxFile::class, $file);
		$this->assertNotEmpty($file->tracks, 'File should have tracks');
		
		// Assert - Track has statistics
		$track = $file->tracks[0];
		$this->assertNotNull($track->stats, 'Track should have statistics calculated');
		
		// Assert - Distance calculated
		$this->assertGreaterThan(0, $track->stats->distance, 'Distance should be greater than 0');
		$this->assertIsFloat($track->stats->distance, 'Distance should be a float');
		
		// Assert - Duration calculated
		$this->assertGreaterThan(0, $track->stats->duration, 'Duration should be greater than 0');
		$this->assertIsInt($track->stats->duration, 'Duration should be an integer (seconds)');
		
		// Assert - Start and finish times set
		$this->assertNotNull($track->stats->startedAt, 'Start time should be set');
		$this->assertInstanceOf(\DateTime::class, $track->stats->startedAt);
		$this->assertNotNull($track->stats->finishedAt, 'Finish time should be set');
		$this->assertInstanceOf(\DateTime::class, $track->stats->finishedAt);
		
		// Assert - Elevation statistics calculated
		$this->assertNotNull($track->stats->minAltitude, 'Min altitude should be calculated');
		$this->assertNotNull($track->stats->maxAltitude, 'Max altitude should be calculated');
		$this->assertLessThanOrEqual(
			$track->stats->maxAltitude,
			$track->stats->minAltitude,
			'Min altitude should be less than or equal to max altitude'
		);
		
		// Assert - Average speed calculated
		if ($track->stats->duration > 0) {
			$this->assertNotNull($track->stats->averageSpeed, 'Average speed should be calculated');
			$this->assertGreaterThan(0, $track->stats->averageSpeed, 'Average speed should be positive');
		}
	}

	/**
	 * Test statistics match expected values for known track.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_match_expected_values_for_known_track(): void
	{
		// Arrange - Create a track with known values
		$gpxFile = new GpxFile('phpGPX Stats Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Known Track Test']);
		
		$track = new Track();
		$track->name = 'Known Track';
		
		$segment = new Segment();
		
		// Point 1: Start at (54.0, 9.0), elevation 100m, time 12:00:00
		$point1 = new Point(Point::TRACKPOINT);
		$point1->latitude = 54.0;
		$point1->longitude = 9.0;
		$point1->elevation = 100.0;
		$point1->time = new \DateTime('2024-01-01T12:00:00Z');
		$segment->points[] = $point1;
		
		// Point 2: Move to (54.01, 9.01), elevation 110m, time 12:01:00 (60 seconds later)
		$point2 = new Point(Point::TRACKPOINT);
		$point2->latitude = 54.01;
		$point2->longitude = 9.01;
		$point2->elevation = 110.0;
		$point2->time = new \DateTime('2024-01-01T12:01:00Z');
		$segment->points[] = $point2;
		
		// Point 3: Move to (54.02, 9.02), elevation 105m, time 12:02:00 (120 seconds total)
		$point3 = new Point(Point::TRACKPOINT);
		$point3->latitude = 54.02;
		$point3->longitude = 9.02;
		$point3->elevation = 105.0;
		$point3->time = new \DateTime('2024-01-01T12:02:00Z');
		$segment->points[] = $point3;
		
		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;
		
		// Enable stats calculation
		phpGPX::$CALCULATE_STATS = true;
		
		// Act - Save and reload to trigger stats calculation
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_stats_test_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$loadedFile = $gpx->load($tempFile);
		
		// Assert - Statistics calculated
		$loadedTrack = $loadedFile->tracks[0];
		$this->assertNotNull($loadedTrack->stats, 'Track should have statistics');
		
		// Assert - Duration is 120 seconds (2 minutes)
		$this->assertEquals(120, $loadedTrack->stats->duration, 'Duration should be 120 seconds');
		
		// Assert - Start and finish times correct
		$this->assertEquals(
			'2024-01-01T12:00:00+00:00',
			$loadedTrack->stats->startedAt->format('c'),
			'Start time should match first point'
		);
		$this->assertEquals(
			'2024-01-01T12:02:00+00:00',
			$loadedTrack->stats->finishedAt->format('c'),
			'Finish time should match last point'
		);
		
		// Assert - Elevation range correct
		$this->assertEquals(100.0, $loadedTrack->stats->minAltitude, 'Min altitude should be 100m');
		$this->assertEquals(110.0, $loadedTrack->stats->maxAltitude, 'Max altitude should be 110m');
		
		// Assert - Elevation gain/loss calculated
		$this->assertNotNull($loadedTrack->stats->cumulativeElevationGain, 'Elevation gain should be calculated');
		$this->assertNotNull($loadedTrack->stats->cumulativeElevationLoss, 'Elevation loss should be calculated');
		
		// Elevation gain: 100 -> 110 = 10m gain
		// Elevation loss: 110 -> 105 = 5m loss
		$this->assertGreaterThanOrEqual(10.0, $loadedTrack->stats->cumulativeElevationGain, 'Should have at least 10m gain');
		$this->assertGreaterThanOrEqual(5.0, $loadedTrack->stats->cumulativeElevationLoss, 'Should have at least 5m loss');
		
		// Assert - Distance is positive
		$this->assertGreaterThan(0, $loadedTrack->stats->distance, 'Distance should be positive');
		
		// Assert - Average speed calculated
		$this->assertNotNull($loadedTrack->stats->averageSpeed, 'Average speed should be calculated');
		$this->assertGreaterThan(0, $loadedTrack->stats->averageSpeed, 'Average speed should be positive');
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test statistics calculation with multiple segments.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_calculation_with_multiple_segments(): void
	{
		// Arrange - Create track with 2 segments
		$gpxFile = new GpxFile('phpGPX Stats Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Multi-Segment Stats Test']);
		
		$track = new Track();
		$track->name = 'Multi-Segment Track';
		
		// Segment 1
		$segment1 = new Segment();
		$point1 = PointFactory::create([
			'latitude' => 54.0,
			'longitude' => 9.0,
			'elevation' => 100.0,
			'time' => new \DateTime('2024-01-01T12:00:00Z'),
		]);
		$point2 = PointFactory::create([
			'latitude' => 54.01,
			'longitude' => 9.01,
			'elevation' => 110.0,
			'time' => new \DateTime('2024-01-01T12:01:00Z'),
		]);
		$segment1->points[] = $point1;
		$segment1->points[] = $point2;
		$track->segments[] = $segment1;
		
		// Segment 2
		$segment2 = new Segment();
		$point3 = PointFactory::create([
			'latitude' => 54.02,
			'longitude' => 9.02,
			'elevation' => 120.0,
			'time' => new \DateTime('2024-01-01T12:02:00Z'),
		]);
		$point4 = PointFactory::create([
			'latitude' => 54.03,
			'longitude' => 9.03,
			'elevation' => 115.0,
			'time' => new \DateTime('2024-01-01T12:03:00Z'),
		]);
		$segment2->points[] = $point3;
		$segment2->points[] = $point4;
		$track->segments[] = $segment2;
		
		$gpxFile->tracks[] = $track;
		
		// Act - Save and reload with stats calculation
		phpGPX::$CALCULATE_STATS = true;
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_multi_segment_stats_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$loadedFile = $gpx->load($tempFile);
		
		// Assert - Track statistics aggregate both segments
		$loadedTrack = $loadedFile->tracks[0];
		$this->assertNotNull($loadedTrack->stats, 'Track should have statistics');
		
		// Duration should span all segments
		$this->assertEquals(180, $loadedTrack->stats->duration, 'Duration should be 180 seconds (3 minutes)');
		
		// Start time from first segment, finish time from last segment
		$this->assertEquals(
			'2024-01-01T12:00:00+00:00',
			$loadedTrack->stats->startedAt->format('c')
		);
		$this->assertEquals(
			'2024-01-01T12:03:00+00:00',
			$loadedTrack->stats->finishedAt->format('c')
		);
		
		// Elevation range across all segments
		$this->assertEquals(100.0, $loadedTrack->stats->minAltitude);
		$this->assertEquals(120.0, $loadedTrack->stats->maxAltitude);
		
		// Distance should be sum of both segments
		$this->assertGreaterThan(0, $loadedTrack->stats->distance);
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test statistics calculation with track containing no time data.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_calculation_without_time_data(): void
	{
		// Arrange - Create track without time data
		$gpxFile = new GpxFile('phpGPX Stats Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'No Time Data Test']);
		
		$track = new Track();
		$segment = new Segment();
		
		// Points without time
		$point1 = PointFactory::create([
			'latitude' => 54.0,
			'longitude' => 9.0,
			'elevation' => 100.0,
			'time' => null,
		]);
		$point2 = PointFactory::create([
			'latitude' => 54.01,
			'longitude' => 9.01,
			'elevation' => 110.0,
			'time' => null,
		]);
		
		$segment->points[] = $point1;
		$segment->points[] = $point2;
		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;
		
		// Act - Save and reload
		phpGPX::$CALCULATE_STATS = true;
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_no_time_stats_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$loadedFile = $gpx->load($tempFile);
		
		// Assert - Statistics still calculated (distance, elevation)
		$loadedTrack = $loadedFile->tracks[0];
		$this->assertNotNull($loadedTrack->stats, 'Track should have statistics');
		
		// Distance should still be calculated
		$this->assertGreaterThan(0, $loadedTrack->stats->distance, 'Distance should be calculated without time');
		
		// Elevation statistics should be calculated
		$this->assertEquals(100.0, $loadedTrack->stats->minAltitude);
		$this->assertEquals(110.0, $loadedTrack->stats->maxAltitude);
		
		// Time-based statistics may be null
		// (duration, average speed, etc. require time data)
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test statistics calculation with real-world GPS track.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_calculation_with_real_world_track(): void
	{
		// Arrange
		$gpx = new phpGPX();
		phpGPX::$CALCULATE_STATS = true;
		$filePath = $this->getFixturePath('gps-track.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert - File loaded and has tracks
		$this->assertNotEmpty($file->tracks, 'Real-world file should have tracks');
		
		// Check each track has statistics
		foreach ($file->tracks as $track) {
			$this->assertNotNull($track->stats, 'Each track should have statistics');
			
			// Basic sanity checks on statistics
			if ($track->stats->distance !== null) {
				$this->assertGreaterThanOrEqual(0, $track->stats->distance, 'Distance should be non-negative');
			}
			
			if ($track->stats->duration !== null) {
				$this->assertGreaterThanOrEqual(0, $track->stats->duration, 'Duration should be non-negative');
			}
			
			if ($track->stats->minAltitude !== null && $track->stats->maxAltitude !== null) {
				$this->assertLessThanOrEqual(
					$track->stats->maxAltitude,
					$track->stats->minAltitude,
					'Min altitude should be <= max altitude'
				);
			}
			
			if ($track->stats->cumulativeElevationGain !== null) {
				$this->assertGreaterThanOrEqual(
					0,
					$track->stats->cumulativeElevationGain,
					'Elevation gain should be non-negative'
				);
			}
			
			if ($track->stats->cumulativeElevationLoss !== null) {
				$this->assertGreaterThanOrEqual(
					0,
					$track->stats->cumulativeElevationLoss,
					'Elevation loss should be non-negative'
				);
			}
		}
	}

	/**
	 * Test statistics calculation with empty track.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_calculation_with_empty_track(): void
	{
		// Arrange - Create track with no points
		$gpxFile = new GpxFile('phpGPX Stats Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Empty Track Test']);
		
		$track = new Track();
		$track->name = 'Empty Track';
		$segment = new Segment();
		// No points added
		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;
		
		// Act - Save and reload
		phpGPX::$CALCULATE_STATS = true;
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_empty_stats_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$loadedFile = $gpx->load($tempFile);
		
		// Assert - Track loaded
		$this->assertNotEmpty($loadedFile->tracks, 'Should have track');
		$loadedTrack = $loadedFile->tracks[0];
		
		// Statistics may be null or have zero values for empty track
		// This is acceptable behavior
		if ($loadedTrack->stats !== null) {
			// If stats exist, they should have sensible default values
			$this->assertGreaterThanOrEqual(0, $loadedTrack->stats->distance ?? 0);
			$this->assertGreaterThanOrEqual(0, $loadedTrack->stats->duration ?? 0);
		}
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test statistics calculation with single point track.
	 * 
	 * Requirements: 4.2
	 */
	public function test_statistics_calculation_with_single_point(): void
	{
		// Arrange - Create track with single point
		$gpxFile = new GpxFile('phpGPX Stats Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Single Point Test']);
		
		$track = new Track();
		$segment = new Segment();
		
		$point = PointFactory::create([
			'latitude' => 54.0,
			'longitude' => 9.0,
			'elevation' => 100.0,
			'time' => new \DateTime('2024-01-01T12:00:00Z'),
		]);
		$segment->points[] = $point;
		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;
		
		// Act - Save and reload
		phpGPX::$CALCULATE_STATS = true;
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_single_point_stats_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$loadedFile = $gpx->load($tempFile);
		
		// Assert - Statistics calculated
		$loadedTrack = $loadedFile->tracks[0];
		$this->assertNotNull($loadedTrack->stats, 'Track should have statistics');
		
		// Distance should be 0 (only one point)
		$this->assertEquals(0, $loadedTrack->stats->distance, 'Distance should be 0 for single point');
		
		// Duration should be 0 (only one point)
		$this->assertEquals(0, $loadedTrack->stats->duration, 'Duration should be 0 for single point');
		
		// Elevation should match the single point
		$this->assertEquals(100.0, $loadedTrack->stats->minAltitude);
		$this->assertEquals(100.0, $loadedTrack->stats->maxAltitude);
		
		// Start and finish should be the same
		$this->assertEquals(
			$loadedTrack->stats->startedAt->format('c'),
			$loadedTrack->stats->finishedAt->format('c')
		);
		
		// Cleanup
		unlink($tempFile);
	}
}
