<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Helpers;

use phpGPX\Helpers\DistanceCalculator;
use phpGPX\Models\Point;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for DistanceCalculator.
 */
final class DistanceCalculatorTest extends TestCase
{
	/**
	 * Test distance accumulation across a sequence of points.
	 */
	public function test_calculate_distance_accumulation_across_point_sequence(): void
	{
		// Arrange - Create a sequence of points along a path
		$points = PointFactory::createSequence(5, 54.0, 9.0, 0.01);
		$calculator = new DistanceCalculator($points);

		// Act
		$distance = $calculator->getRawDistance();

		// Assert - Distance should be positive and reasonable
		$this->assertGreaterThan(0, $distance);
		// Each step is ~0.01 degrees, roughly 1.1km per step, 4 steps total
		$this->assertGreaterThan(4000, $distance); // At least 4km
		$this->assertLessThan(6000, $distance); // Less than 6km
	}

	/**
	 * Test distance calculation with elevation (real distance).
	 */
	public function test_calculate_real_distance_with_elevation(): void
	{
		// Arrange - Create points with elevation
		$point1 = PointFactory::create([
			'latitude' => 54.0,
			'longitude' => 9.0,
			'elevation' => 100.0,
		]);
		$point2 = PointFactory::create([
			'latitude' => 54.01,
			'longitude' => 9.01,
			'elevation' => 150.0,
		]);
		$point3 = PointFactory::create([
			'latitude' => 54.02,
			'longitude' => 9.02,
			'elevation' => 200.0,
		]);

		$calculator = new DistanceCalculator([$point1, $point2, $point3]);

		// Act
		$realDistance = $calculator->getRealDistance();
		$rawDistance = $calculator->getRawDistance();

		// Assert - Real distance should be greater than raw distance due to elevation
		$this->assertGreaterThan($rawDistance, $realDistance);
		$this->assertGreaterThan(0, $realDistance);
	}

	/**
	 * Test handling of empty point sequence.
	 */
	public function test_calculate_distance_with_empty_point_sequence(): void
	{
		// Arrange
		$calculator = new DistanceCalculator([]);

		// Act
		$distance = $calculator->getRawDistance();

		// Assert - Empty sequence should return 0 distance
		$this->assertEquals(0, $distance);
	}

	/**
	 * Test handling of single point.
	 */
	public function test_calculate_distance_with_single_point(): void
	{
		// Arrange
		$point = PointFactory::create();
		$calculator = new DistanceCalculator([$point]);

		// Act
		$distance = $calculator->getRawDistance();

		// Assert - Single point should return 0 distance
		$this->assertEquals(0, $distance);
	}

	/**
	 * Test handling of two points.
	 */
	public function test_calculate_distance_with_two_points(): void
	{
		// Arrange
		$point1 = PointFactory::createAtCoordinates(54.0, 9.0);
		$point2 = PointFactory::createAtCoordinates(54.01, 9.01);
		$calculator = new DistanceCalculator([$point1, $point2]);

		// Act
		$distance = $calculator->getRawDistance();

		// Assert - Distance should be positive
		$this->assertGreaterThan(0, $distance);
		// Roughly 1.1-1.5km for 0.01 degree difference
		$this->assertGreaterThan(1000, $distance);
		$this->assertLessThan(2000, $distance);
	}

	/**
	 * Test that distance is accumulated correctly across multiple segments.
	 */
	public function test_distance_accumulation_is_cumulative(): void
	{
		// Arrange - Create 3 points in a line
		$point1 = PointFactory::createAtCoordinates(54.0, 9.0);
		$point2 = PointFactory::createAtCoordinates(54.01, 9.0);
		$point3 = PointFactory::createAtCoordinates(54.02, 9.0);

		$calculator = new DistanceCalculator([$point1, $point2, $point3]);

		// Act
		$totalDistance = $calculator->getRawDistance();

		// Assert - Total distance should be roughly 2x the distance between adjacent points
		// Calculate expected distance between point1 and point2
		$calculatorTwoPoints = new DistanceCalculator([$point1, $point2]);
		$singleSegmentDistance = $calculatorTwoPoints->getRawDistance();

		// Total should be approximately 2x single segment (with small tolerance)
		$this->assertEqualsWithDelta($singleSegmentDistance * 2, $totalDistance, 10);
	}

	/**
	 * Test handling of points with null coordinates is no longer possible.
	 * With GPX 1.1 schema compliance, Points require latitude and longitude in constructor.
	 * This test has been removed as it validated invalid behavior.
	 *
	 * Requirements: 10.1, 10.6
	 */
	// Test removed - Points now require coordinates in constructor

	/**
	 * Test that point objects are updated with distance information.
	 */
	public function test_points_are_updated_with_distance_information(): void
	{
		// Arrange
		$point1 = PointFactory::createAtCoordinates(54.0, 9.0);
		$point2 = PointFactory::createAtCoordinates(54.01, 9.0);
		$point3 = PointFactory::createAtCoordinates(54.02, 9.0);

		$calculator = new DistanceCalculator([$point1, $point2, $point3]);

		// Act
		$calculator->getRawDistance();

		// Assert - Points should have distance and difference properties set
		$this->assertNull($point1->distance); // First point has no distance
		$this->assertNull($point1->difference); // First point has no difference

		$this->assertNotNull($point2->distance);
		$this->assertNotNull($point2->difference);
		$this->assertGreaterThan(0, $point2->distance);
		$this->assertGreaterThan(0, $point2->difference);

		$this->assertNotNull($point3->distance);
		$this->assertNotNull($point3->difference);
		$this->assertGreaterThan($point2->distance, $point3->distance);
	}
}
