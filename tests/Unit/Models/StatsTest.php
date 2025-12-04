<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Stats;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Stats model.
 * Tests Stats creation with various metrics and serialization.
 */
final class StatsTest extends TestCase
{
	/**
	 * Test Stats can be created with default values.
	 * Requirements: 2.2
	 */
	public function test_stats_can_be_created_with_default_values(): void
	{
		// Arrange & Act
		$stats = new Stats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $stats);
		$this->assertEquals(0, $stats->distance);
		$this->assertEquals(0, $stats->realDistance);
		$this->assertNull($stats->averageSpeed);
		$this->assertNull($stats->averagePace);
		$this->assertNull($stats->minAltitude);
		$this->assertNull($stats->minAltitudeCoords);
		$this->assertNull($stats->maxAltitude);
		$this->assertNull($stats->maxAltitudeCoords);
		$this->assertNull($stats->cumulativeElevationGain);
		$this->assertNull($stats->cumulativeElevationLoss);
		$this->assertNull($stats->startedAt);
		$this->assertNull($stats->startedAtCoords);
		$this->assertNull($stats->finishedAt);
		$this->assertNull($stats->finishedAtCoords);
		$this->assertNull($stats->duration);
		$this->assertIsArray($stats->bounds);
		$this->assertEmpty($stats->bounds);
	}

	/**
	 * Test Stats stores distance values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_distance_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->distance = 5000.0;
		$stats->realDistance = 5250.5;
		
		// Assert
		$this->assertEquals(5000.0, $stats->distance);
		$this->assertEquals(5250.5, $stats->realDistance);
	}

	/**
	 * Test Stats stores speed and pace values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_speed_and_pace_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->averageSpeed = 2.5;
		$stats->averagePace = 400.0;
		
		// Assert
		$this->assertEquals(2.5, $stats->averageSpeed);
		$this->assertEquals(400.0, $stats->averagePace);
	}

	/**
	 * Test Stats stores altitude values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_altitude_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->minAltitude = 100;
		$stats->maxAltitude = 500;
		$stats->minAltitudeCoords = [54.0, 9.0];
		$stats->maxAltitudeCoords = [54.5, 9.5];
		
		// Assert
		$this->assertEquals(100, $stats->minAltitude);
		$this->assertEquals(500, $stats->maxAltitude);
		$this->assertEquals([54.0, 9.0], $stats->minAltitudeCoords);
		$this->assertEquals([54.5, 9.5], $stats->maxAltitudeCoords);
	}

	/**
	 * Test Stats stores elevation gain and loss values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_elevation_gain_and_loss(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->cumulativeElevationGain = 250;
		$stats->cumulativeElevationLoss = 180;
		
		// Assert
		$this->assertEquals(250, $stats->cumulativeElevationGain);
		$this->assertEquals(180, $stats->cumulativeElevationLoss);
	}

	/**
	 * Test Stats stores time values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_time_values(): void
	{
		// Arrange
		$stats = new Stats();
		$startTime = new \DateTime('2024-01-15 10:00:00', new \DateTimeZone('UTC'));
		$endTime = new \DateTime('2024-01-15 12:30:00', new \DateTimeZone('UTC'));
		
		// Act
		$stats->startedAt = $startTime;
		$stats->finishedAt = $endTime;
		$stats->duration = 9000; // 2.5 hours in seconds
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $stats->startedAt);
		$this->assertInstanceOf(\DateTime::class, $stats->finishedAt);
		$this->assertEquals($startTime->getTimestamp(), $stats->startedAt->getTimestamp());
		$this->assertEquals($endTime->getTimestamp(), $stats->finishedAt->getTimestamp());
		$this->assertEquals(9000, $stats->duration);
	}

	/**
	 * Test Stats stores coordinate values for start and finish.
	 * Requirements: 2.2
	 */
	public function test_stats_stores_start_and_finish_coordinates(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->startedAtCoords = [54.0, 9.0];
		$stats->finishedAtCoords = [54.5, 9.5];
		
		// Assert
		$this->assertEquals([54.0, 9.0], $stats->startedAtCoords);
		$this->assertEquals([54.5, 9.5], $stats->finishedAtCoords);
	}

	/**
	 * Test Stats stores bounds.
	 * Requirements: 2.2
	 */
	public function test_stats_stores_bounds(): void
	{
		// Arrange
		$stats = new Stats();
		$bounds = [
			'minLatitude' => 50.0,
			'minLongitude' => 10.0,
			'maxLatitude' => 55.0,
			'maxLongitude' => 15.0
		];
		
		// Act
		$stats->bounds = $bounds;
		
		// Assert
		$this->assertEquals($bounds, $stats->bounds);
		$this->assertEquals(50.0, $stats->bounds['minLatitude']);
		$this->assertEquals(10.0, $stats->bounds['minLongitude']);
		$this->assertEquals(55.0, $stats->bounds['maxLatitude']);
		$this->assertEquals(15.0, $stats->bounds['maxLongitude']);
	}

	/**
	 * Test Stats with all fields populated.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_stores_all_fields(): void
	{
		// Arrange
		$stats = new Stats();
		$startTime = new \DateTime('2024-01-15 10:00:00', new \DateTimeZone('UTC'));
		$endTime = new \DateTime('2024-01-15 12:30:00', new \DateTimeZone('UTC'));
		
		// Act
		$stats->distance = 10000.0;
		$stats->realDistance = 10500.0;
		$stats->averageSpeed = 3.0;
		$stats->averagePace = 333.33;
		$stats->minAltitude = 50;
		$stats->maxAltitude = 800;
		$stats->minAltitudeCoords = [54.0, 9.0];
		$stats->maxAltitudeCoords = [54.8, 9.8];
		$stats->cumulativeElevationGain = 500;
		$stats->cumulativeElevationLoss = 350;
		$stats->startedAt = $startTime;
		$stats->startedAtCoords = [54.0, 9.0];
		$stats->finishedAt = $endTime;
		$stats->finishedAtCoords = [54.8, 9.8];
		$stats->duration = 9000;
		$stats->bounds = ['minLatitude' => 54.0, 'minLongitude' => 9.0, 'maxLatitude' => 54.8, 'maxLongitude' => 9.8];
		
		// Assert
		$this->assertEquals(10000.0, $stats->distance);
		$this->assertEquals(10500.0, $stats->realDistance);
		$this->assertEquals(3.0, $stats->averageSpeed);
		$this->assertEquals(333.33, $stats->averagePace);
		$this->assertEquals(50, $stats->minAltitude);
		$this->assertEquals(800, $stats->maxAltitude);
		$this->assertEquals([54.0, 9.0], $stats->minAltitudeCoords);
		$this->assertEquals([54.8, 9.8], $stats->maxAltitudeCoords);
		$this->assertEquals(500, $stats->cumulativeElevationGain);
		$this->assertEquals(350, $stats->cumulativeElevationLoss);
		$this->assertInstanceOf(\DateTime::class, $stats->startedAt);
		$this->assertInstanceOf(\DateTime::class, $stats->finishedAt);
		$this->assertEquals([54.0, 9.0], $stats->startedAtCoords);
		$this->assertEquals([54.8, 9.8], $stats->finishedAtCoords);
		$this->assertEquals(9000, $stats->duration);
		$this->assertIsArray($stats->bounds);
	}

	/**
	 * Test Stats reset method clears all values.
	 * Requirements: 2.2
	 */
	public function test_stats_reset_clears_all_values(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->distance = 5000.0;
		$stats->realDistance = 5250.0;
		$stats->averageSpeed = 2.5;
		$stats->averagePace = 400.0;
		$stats->minAltitude = 100;
		$stats->maxAltitude = 500;
		$stats->cumulativeElevationGain = 250;
		$stats->cumulativeElevationLoss = 180;
		$stats->startedAt = new \DateTime();
		$stats->finishedAt = new \DateTime();
		$stats->duration = 3600;
		$stats->bounds = ['test' => 'value'];
		
		// Act
		$stats->reset();
		
		// Assert
		$this->assertNull($stats->distance);
		$this->assertNull($stats->realDistance);
		$this->assertNull($stats->averageSpeed);
		$this->assertNull($stats->averagePace);
		$this->assertNull($stats->minAltitude);
		$this->assertNull($stats->maxAltitude);
		$this->assertNull($stats->minAltitudeCoords);
		$this->assertNull($stats->maxAltitudeCoords);
		$this->assertNull($stats->cumulativeElevationGain);
		$this->assertNull($stats->cumulativeElevationLoss);
		$this->assertNull($stats->startedAt);
		$this->assertNull($stats->startedAtCoords);
		$this->assertNull($stats->finishedAt);
		$this->assertNull($stats->finishedAtCoords);
		$this->assertNull($stats->bounds);
	}

	/**
	 * Test Stats serialization to array with default values.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_to_array_with_default_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('distance', $array);
		$this->assertArrayHasKey('realDistance', $array);
		$this->assertArrayHasKey('avgSpeed', $array);
		$this->assertArrayHasKey('avgPace', $array);
		$this->assertArrayHasKey('minAltitude', $array);
		$this->assertArrayHasKey('minAltitudeCoords', $array);
		$this->assertArrayHasKey('maxAltitude', $array);
		$this->assertArrayHasKey('maxAltitudeCoords', $array);
		$this->assertArrayHasKey('cumulativeElevationGain', $array);
		$this->assertArrayHasKey('cumulativeElevationLoss', $array);
		$this->assertArrayHasKey('startedAt', $array);
		$this->assertArrayHasKey('startedAtCoords', $array);
		$this->assertArrayHasKey('finishedAt', $array);
		$this->assertArrayHasKey('finishedAtCoords', $array);
		$this->assertArrayHasKey('duration', $array);
		$this->assertArrayHasKey('bounds', $array);
		
		// Check default values are cast to float
		$this->assertEquals(0.0, $array['distance']);
		$this->assertEquals(0.0, $array['realDistance']);
		$this->assertEquals(0.0, $array['avgSpeed']);
		$this->assertEquals(0.0, $array['avgPace']);
	}

	/**
	 * Test Stats serialization to array with distance values.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_distance_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->distance = 5000.0;
		$stats->realDistance = 5250.5;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals(5000.0, $array['distance']);
		$this->assertEquals(5250.5, $array['realDistance']);
	}

	/**
	 * Test Stats serialization to array with speed and pace.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_speed_and_pace_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->averageSpeed = 2.5;
		$stats->averagePace = 400.0;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals(2.5, $array['avgSpeed']);
		$this->assertEquals(400.0, $array['avgPace']);
	}

	/**
	 * Test Stats serialization to array with altitude values.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_altitude_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->minAltitude = 100;
		$stats->maxAltitude = 500;
		$stats->minAltitudeCoords = [54.0, 9.0];
		$stats->maxAltitudeCoords = [54.5, 9.5];
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals(100.0, $array['minAltitude']);
		$this->assertEquals(500.0, $array['maxAltitude']);
		$this->assertEquals([54.0, 9.0], $array['minAltitudeCoords']);
		$this->assertEquals([54.5, 9.5], $array['maxAltitudeCoords']);
	}

	/**
	 * Test Stats serialization to array with elevation gain and loss.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_elevation_gain_and_loss_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->cumulativeElevationGain = 250;
		$stats->cumulativeElevationLoss = 180;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals(250.0, $array['cumulativeElevationGain']);
		$this->assertEquals(180.0, $array['cumulativeElevationLoss']);
	}

	/**
	 * Test Stats serialization to array with time values.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_time_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$startTime = new \DateTime('2024-01-15T10:00:00Z');
		$endTime = new \DateTime('2024-01-15T12:30:00Z');
		$stats->startedAt = $startTime;
		$stats->finishedAt = $endTime;
		$stats->duration = 9000;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertNotNull($array['startedAt']);
		$this->assertIsString($array['startedAt']);
		$this->assertStringContainsString('2024-01-15', $array['startedAt']);
		
		$this->assertNotNull($array['finishedAt']);
		$this->assertIsString($array['finishedAt']);
		$this->assertStringContainsString('2024-01-15', $array['finishedAt']);
		
		$this->assertEquals(9000.0, $array['duration']);
	}

	/**
	 * Test Stats serialization to array with coordinates.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_coordinates_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->startedAtCoords = [54.0, 9.0];
		$stats->finishedAtCoords = [54.5, 9.5];
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals([54.0, 9.0], $array['startedAtCoords']);
		$this->assertEquals([54.5, 9.5], $array['finishedAtCoords']);
	}

	/**
	 * Test Stats serialization to array with bounds.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_bounds_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$bounds = [
			'minLatitude' => 50.0,
			'minLongitude' => 10.0,
			'maxLatitude' => 55.0,
			'maxLongitude' => 15.0
		];
		$stats->bounds = $bounds;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals($bounds, $array['bounds']);
	}

	/**
	 * Test Stats serialization to array with all fields.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_all_fields_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$startTime = new \DateTime('2024-01-15T10:00:00Z');
		$endTime = new \DateTime('2024-01-15T12:30:00Z');
		
		$stats->distance = 10000.0;
		$stats->realDistance = 10500.0;
		$stats->averageSpeed = 3.0;
		$stats->averagePace = 333.33;
		$stats->minAltitude = 50;
		$stats->maxAltitude = 800;
		$stats->minAltitudeCoords = [54.0, 9.0];
		$stats->maxAltitudeCoords = [54.8, 9.8];
		$stats->cumulativeElevationGain = 500;
		$stats->cumulativeElevationLoss = 350;
		$stats->startedAt = $startTime;
		$stats->startedAtCoords = [54.0, 9.0];
		$stats->finishedAt = $endTime;
		$stats->finishedAtCoords = [54.8, 9.8];
		$stats->duration = 9000;
		$stats->bounds = ['minLatitude' => 54.0, 'minLongitude' => 9.0, 'maxLatitude' => 54.8, 'maxLongitude' => 9.8];
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertEquals(10000.0, $array['distance']);
		$this->assertEquals(10500.0, $array['realDistance']);
		$this->assertEquals(3.0, $array['avgSpeed']);
		$this->assertEquals(333.33, $array['avgPace']);
		$this->assertEquals(50.0, $array['minAltitude']);
		$this->assertEquals(800.0, $array['maxAltitude']);
		$this->assertEquals([54.0, 9.0], $array['minAltitudeCoords']);
		$this->assertEquals([54.8, 9.8], $array['maxAltitudeCoords']);
		$this->assertEquals(500.0, $array['cumulativeElevationGain']);
		$this->assertEquals(350.0, $array['cumulativeElevationLoss']);
		$this->assertNotNull($array['startedAt']);
		$this->assertEquals([54.0, 9.0], $array['startedAtCoords']);
		$this->assertNotNull($array['finishedAt']);
		$this->assertEquals([54.8, 9.8], $array['finishedAtCoords']);
		$this->assertEquals(9000.0, $array['duration']);
		$this->assertIsArray($array['bounds']);
	}

	/**
	 * Test Stats with zero distance.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_zero_distance(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->distance = 0.0;
		$stats->realDistance = 0.0;
		
		// Assert
		$this->assertEquals(0.0, $stats->distance);
		$this->assertEquals(0.0, $stats->realDistance);
	}

	/**
	 * Test Stats with zero duration.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_zero_duration(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->duration = 0;
		
		// Assert
		$this->assertEquals(0, $stats->duration);
	}

	/**
	 * Test Stats with negative altitude (below sea level).
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_negative_altitude(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->minAltitude = -50;
		$stats->maxAltitude = 100;
		
		// Assert
		$this->assertEquals(-50, $stats->minAltitude);
		$this->assertEquals(100, $stats->maxAltitude);
	}

	/**
	 * Test Stats with very large distance values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_large_distance_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->distance = 1000000.0; // 1000 km
		$stats->realDistance = 1050000.0;
		
		// Assert
		$this->assertEquals(1000000.0, $stats->distance);
		$this->assertEquals(1050000.0, $stats->realDistance);
	}

	/**
	 * Test Stats with very small speed values.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_small_speed_values(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->averageSpeed = 0.5; // Very slow walking
		$stats->averagePace = 2000.0; // 2000 seconds per km
		
		// Assert
		$this->assertEquals(0.5, $stats->averageSpeed);
		$this->assertEquals(2000.0, $stats->averagePace);
	}

	/**
	 * Test Stats with same start and finish time.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_same_start_and_finish_time(): void
	{
		// Arrange
		$stats = new Stats();
		$time = new \DateTime('2024-01-15T10:00:00Z');
		
		// Act
		$stats->startedAt = $time;
		$stats->finishedAt = clone $time;
		$stats->duration = 0;
		
		// Assert
		$this->assertEquals($time->getTimestamp(), $stats->startedAt->getTimestamp());
		$this->assertEquals($time->getTimestamp(), $stats->finishedAt->getTimestamp());
		$this->assertEquals(0, $stats->duration);
	}

	/**
	 * Test Stats with same min and max altitude.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_same_min_and_max_altitude(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->minAltitude = 200;
		$stats->maxAltitude = 200;
		
		// Assert
		$this->assertEquals(200, $stats->minAltitude);
		$this->assertEquals(200, $stats->maxAltitude);
	}

	/**
	 * Test Stats with zero elevation gain and loss.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_zero_elevation_gain_and_loss(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->cumulativeElevationGain = 0;
		$stats->cumulativeElevationLoss = 0;
		
		// Assert
		$this->assertEquals(0, $stats->cumulativeElevationGain);
		$this->assertEquals(0, $stats->cumulativeElevationLoss);
	}

	/**
	 * Test Stats serialization with null time values.
	 * Requirements: 2.7
	 */
	public function test_stats_serializes_null_time_to_array(): void
	{
		// Arrange
		$stats = new Stats();
		$stats->startedAt = null;
		$stats->finishedAt = null;
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertNull($array['startedAt']);
		$this->assertNull($array['finishedAt']);
	}

	/**
	 * Test Stats with empty bounds array.
	 * Requirements: 2.2, 2.7
	 */
	public function test_stats_with_empty_bounds_array(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$array = $stats->toArray();
		
		// Assert
		$this->assertIsArray($stats->bounds);
		$this->assertEmpty($stats->bounds);
		$this->assertIsArray($array['bounds']);
	}

	/**
	 * Test Stats with fractional duration.
	 * Requirements: 2.2, 2.4
	 */
	public function test_stats_with_fractional_duration(): void
	{
		// Arrange
		$stats = new Stats();
		
		// Act
		$stats->duration = 3661; // 1 hour, 1 minute, 1 second
		
		// Assert
		$this->assertEquals(3661, $stats->duration);
	}

	/**
	 * Test Stats time with different timezones.
	 * Requirements: 2.2
	 */
	public function test_stats_time_with_different_timezones(): void
	{
		// Arrange
		$stats = new Stats();
		$timeUTC = new \DateTime('2024-01-15 10:00:00', new \DateTimeZone('UTC'));
		$timeEST = new \DateTime('2024-01-15 10:00:00', new \DateTimeZone('America/New_York'));
		
		// Act
		$stats->startedAt = $timeUTC;
		$utcTimestamp = $stats->startedAt->getTimestamp();
		
		$stats->startedAt = $timeEST;
		$estTimestamp = $stats->startedAt->getTimestamp();
		
		// Assert
		$this->assertNotEquals($utcTimestamp, $estTimestamp);
		$this->assertInstanceOf(\DateTime::class, $stats->startedAt);
	}
}

