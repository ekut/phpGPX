<?php

declare(strict_types=1);
/**
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace UnitTests\phpGPX\Helpers;

use Eris\TestTrait;
use phpGPX\Helpers\GeoHelper;
use phpGPX\Models\Point;
use PHPUnit\Framework\TestCase;

class GeoHelperTest extends TestCase
{
	use TestTrait;

	/**
	 * Tested with https://www.freemaptools.com/measure-distance.htm
	 *
	 * Input points:
	 *  - 48.1573923225717 17.0547121910204
	 *  - 48.1644916381763 17.0591753907502
	 */
	public function testGetDistance(): void
	{
		$point1 = new Point(Point::WAYPOINT, 48.1573923225717, 17.0547121910204);
		$point2 = new Point(Point::WAYPOINT, 48.1644916381763, 17.0591753907502);

		$this->assertEqualsWithDelta(
			856.97,
			GeoHelper::getRawDistance($point1, $point2),
			1,
			"Invalid distance between two points!",
		);
	}

	/**
	 * @link http://cosinekitty.com/compass.html
	 */
	public function testRealDistance(): void
	{
		$point1 = new Point(Point::WAYPOINT, 48.1573923225717, 17.0547121910204);
		$point1->elevation = 100;

		$point2 = new Point(Point::WAYPOINT, 48.1644916381763, 17.0591753907502);
		$point2->elevation = 200;

		$this->assertEqualsWithDelta(
			856.97,
			GeoHelper::getRawDistance($point1, $point2),
			1,
			"Invalid distance between two points!",
		);

		$this->assertEqualsWithDelta(
			862,
			GeoHelper::getRealDistance($point1, $point2),
			1,
			"Invalid real distance between two points!",
		);
	}

	/**
	 * Test getRawDistance with known coordinate pairs from different locations.
	 * Using verified distances from geographic calculators.
	 */
	public function test_getRawDistance_with_known_coordinate_pairs_returns_correct_values(): void
	{
		// Test 1: New York to Los Angeles (approximately 3936 km)
		$newYork = new Point(Point::WAYPOINT, 40.7128, -74.0060);
		$losAngeles = new Point(Point::WAYPOINT, 34.0522, -118.2437);

		$distance = GeoHelper::getRawDistance($newYork, $losAngeles);
		$this->assertEqualsWithDelta(3936000, $distance, 10000, "Distance NY to LA should be approximately 3936 km");

		// Test 2: London to Paris (approximately 344 km)
		$london = new Point(Point::WAYPOINT, 51.5074, -0.1278);
		$paris = new Point(Point::WAYPOINT, 48.8566, 2.3522);

		$distance = GeoHelper::getRawDistance($london, $paris);
		$this->assertEqualsWithDelta(344000, $distance, 5000, "Distance London to Paris should be approximately 344 km");

		// Test 3: Sydney to Melbourne (approximately 714 km)
		$sydney = new Point(Point::WAYPOINT, -33.8688, 151.2093);
		$melbourne = new Point(Point::WAYPOINT, -37.8136, 144.9631);

		$distance = GeoHelper::getRawDistance($sydney, $melbourne);
		$this->assertEqualsWithDelta(714000, $distance, 5000, "Distance Sydney to Melbourne should be approximately 714 km");
	}

	/**
	 * Test getRealDistance with elevation differences.
	 */
	public function test_getRealDistance_with_elevation_differences_returns_correct_values(): void
	{
		// Test with significant elevation difference
		$point1 = new Point(Point::WAYPOINT, 45.0, 10.0);
		$point1->elevation = 0;

		$point2 = new Point(Point::WAYPOINT, 45.0, 10.01);
		$point2->elevation = 1000;

		$rawDistance = GeoHelper::getRawDistance($point1, $point2);
		$realDistance = GeoHelper::getRealDistance($point1, $point2);

		// Real distance should be greater than raw distance due to elevation
		$this->assertGreaterThan($rawDistance, $realDistance, "Real distance should be greater than raw distance with elevation");

		// Calculate expected real distance using Pythagorean theorem
		$expectedRealDistance = sqrt(pow($rawDistance, 2) + pow(1000, 2));
		$this->assertEqualsWithDelta($expectedRealDistance, $realDistance, 0.1, "Real distance calculation should match Pythagorean theorem");
	}

	/**
	 * Test getRealDistance with null elevation values.
	 */
	public function test_getRealDistance_with_null_elevation_treats_as_zero(): void
	{
		$point1 = new Point(Point::WAYPOINT, 45.0, 10.0);
		$point1->elevation = null;

		$point2 = new Point(Point::WAYPOINT, 45.0, 10.01);
		$point2->elevation = null;

		$rawDistance = GeoHelper::getRawDistance($point1, $point2);
		$realDistance = GeoHelper::getRealDistance($point1, $point2);

		// With null elevations (treated as 0), real distance should equal raw distance
		$this->assertEqualsWithDelta($rawDistance, $realDistance, 0.1, "Real distance should equal raw distance when elevations are null");
	}

	/**
	 * Test edge case: same point should return zero distance.
	 */
	public function test_getRawDistance_same_point_returns_zero(): void
	{
		$point1 = new Point(Point::WAYPOINT, 48.1573923225717, 17.0547121910204);

		$point2 = new Point(Point::WAYPOINT, 48.1573923225717, 17.0547121910204);

		$distance = GeoHelper::getRawDistance($point1, $point2);
		$this->assertEqualsWithDelta(0, $distance, 0.01, "Distance between same point should be zero");
	}

	/**
	 * Test edge case: antipodal points (opposite sides of Earth).
	 * Antipodal points should have maximum distance (approximately half Earth's circumference).
	 */
	public function test_getRawDistance_antipodal_points_returns_maximum_distance(): void
	{
		// Point in Northern Hemisphere
		$point1 = new Point(Point::WAYPOINT, 45.0, 0.0);

		// Antipodal point (opposite side of Earth) - use 179.9 instead of 180.0
		$point2 = new Point(Point::WAYPOINT, -45.0, 179.9);

		$distance = GeoHelper::getRawDistance($point1, $point2);

		// Maximum distance on Earth's surface is approximately π * Earth's radius
		$maxDistance = M_PI * GeoHelper::EARTH_RADIUS;

		// Distance should be close to maximum (allowing for the fact these aren't perfectly antipodal)
		$this->assertGreaterThan($maxDistance * 0.9, $distance, "Distance between near-antipodal points should be close to maximum");
	}

	/**
	 * Test edge case: equator crossing.
	 */
	public function test_getRawDistance_equator_crossing_calculates_correctly(): void
	{
		// Point north of equator
		$point1 = new Point(Point::WAYPOINT, 10.0, 0.0);

		// Point south of equator
		$point2 = new Point(Point::WAYPOINT, -10.0, 0.0);

		$distance = GeoHelper::getRawDistance($point1, $point2);

		// 20 degrees of latitude at approximately 111 km per degree
		$expectedDistance = 20 * 111000;
		$this->assertEqualsWithDelta($expectedDistance, $distance, 5000, "Distance crossing equator should be approximately 2220 km");
	}

	/**
	 * Test edge case: prime meridian crossing.
	 */
	public function test_getRawDistance_prime_meridian_crossing_calculates_correctly(): void
	{
		// Point west of prime meridian
		$point1 = new Point(Point::WAYPOINT, 51.5, -5.0);

		// Point east of prime meridian
		$point2 = new Point(Point::WAYPOINT, 51.5, 5.0);

		$distance = GeoHelper::getRawDistance($point1, $point2);

		// Should calculate distance correctly across prime meridian
		$this->assertGreaterThan(0, $distance, "Distance crossing prime meridian should be positive");
		$this->assertLessThan(1000000, $distance, "Distance should be reasonable (less than 1000 km)");
	}

	/**
	 * Test edge case: international date line crossing.
	 */
	public function test_getRawDistance_date_line_crossing_calculates_correctly(): void
	{
		// Point west of date line
		$point1 = new Point(Point::WAYPOINT, 0.0, 179.0);

		// Point east of date line
		$point2 = new Point(Point::WAYPOINT, 0.0, -179.0);

		$distance = GeoHelper::getRawDistance($point1, $point2);

		// Should take shortest path (2 degrees), not go around the world
		$expectedDistance = 2 * 111000; // Approximately 222 km at equator
		$this->assertEqualsWithDelta($expectedDistance, $distance, 10000, "Distance crossing date line should take shortest path");
	}

	/**
	 * Test edge case: points at North Pole.
	 */
	public function test_getRawDistance_north_pole_calculates_correctly(): void
	{
		// Point at North Pole
		$point1 = new Point(Point::WAYPOINT, 90.0, 0.0);

		// Another point at North Pole (longitude doesn't matter at poles) - use 179.9 instead of 180.0
		$point2 = new Point(Point::WAYPOINT, 90.0, 179.9);

		$distance = GeoHelper::getRawDistance($point1, $point2);
		$this->assertEqualsWithDelta(0, $distance, 0.01, "Distance between points at North Pole should be zero");
	}

	/**
	 * Test edge case: points at South Pole.
	 */
	public function test_getRawDistance_south_pole_calculates_correctly(): void
	{
		// Point at South Pole
		$point1 = new Point(Point::WAYPOINT, -90.0, 0.0);

		// Another point at South Pole - use 179.9 instead of 180.0
		$point2 = new Point(Point::WAYPOINT, -90.0, 179.9);

		$distance = GeoHelper::getRawDistance($point1, $point2);
		$this->assertEqualsWithDelta(0, $distance, 0.01, "Distance between points at South Pole should be zero");
	}

	/**
	 * Test that getRealDistance handles negative elevation differences correctly.
	 */
	public function test_getRealDistance_with_negative_elevation_difference_calculates_correctly(): void
	{
		// Point at higher elevation
		$point1 = new Point(Point::WAYPOINT, 45.0, 10.0);
		$point1->elevation = 1000;

		// Point at lower elevation
		$point2 = new Point(Point::WAYPOINT, 45.0, 10.01);
		$point2->elevation = 0;

		$realDistance = GeoHelper::getRealDistance($point1, $point2);

		// Should use absolute value of elevation difference
		$rawDistance = GeoHelper::getRawDistance($point1, $point2);
		$expectedRealDistance = sqrt(pow($rawDistance, 2) + pow(1000, 2));

		$this->assertEqualsWithDelta($expectedRealDistance, $realDistance, 0.1, "Real distance should use absolute elevation difference");
	}

	/**
	 * Property test: Triangle inequality for distances.
	 *
	 * **Feature: test-coverage, Property 9: Triangle inequality for distances**
	 *
	 * For any three points A, B, and C, the distance from A to C should be
	 * less than or equal to the distance from A to B plus the distance from B to C.
	 *
	 * This is a fundamental property of distance metrics in Euclidean and spherical geometry.
	 *
	 * **Validates: Requirements 5.2**
	 */
	public function test_property_triangle_inequality_for_distances(): void
	{
		$this
			->withRand('mt_rand')  // REQUIRED: Initialize random generator
			->forAll(
				// Generate six integers for three points (lat, lon for each)
				// We'll scale them to proper float ranges
				\Eris\Generators::choose(-90, 90),   // Point A latitude
				\Eris\Generators::choose(-180, 179), // Point A longitude (must be < 180)
				\Eris\Generators::choose(-90, 90),   // Point B latitude
				\Eris\Generators::choose(-180, 179), // Point B longitude (must be < 180)
				\Eris\Generators::choose(-90, 90),   // Point C latitude
				\Eris\Generators::choose(-180, 179),  // Point C longitude (must be < 180)
			)
		->withMaxSize(100) // Run 100 iterations as specified in design
		->then(function ($latA, $lonA, $latB, $lonB, $latC, $lonC): void {
			// Convert integers to floats with decimal precision
			$latA = (float) $latA + (mt_rand(0, 999999) / 1000000);
			$lonA = (float) $lonA + (mt_rand(0, 999999) / 1000000);
			$latB = (float) $latB + (mt_rand(0, 999999) / 1000000);
			$lonB = (float) $lonB + (mt_rand(0, 999999) / 1000000);
			$latC = (float) $latC + (mt_rand(0, 999999) / 1000000);
			$lonC = (float) $lonC + (mt_rand(0, 999999) / 1000000);

			// Ensure we stay within valid ranges
			$latA = min(90.0, $latA);
			$latB = min(90.0, $latB);
			$latC = min(90.0, $latC);
			$lonA = min(179.999999, $lonA); // Must be < 180.0
			$lonB = min(179.999999, $lonB); // Must be < 180.0
			$lonC = min(179.999999, $lonC); // Must be < 180.0

			// Create three points
			$pointA = new Point(Point::WAYPOINT, $latA, $lonA);

			$pointB = new Point(Point::WAYPOINT, $latB, $lonB);

			$pointC = new Point(Point::WAYPOINT, $latC, $lonC);

			// Calculate distances for the three sides of the triangle
			$distanceAB = GeoHelper::getRawDistance($pointA, $pointB);
			$distanceBC = GeoHelper::getRawDistance($pointB, $pointC);
			$distanceAC = GeoHelper::getRawDistance($pointA, $pointC);

			// Triangle inequality: distance(A, C) <= distance(A, B) + distance(B, C)
			// We use a small epsilon for floating point comparison
			$epsilon = 0.01; // 1 cm tolerance for floating point errors

			$this->assertLessThanOrEqual(
				$distanceAB + $distanceBC + $epsilon,
				$distanceAC,
				sprintf(
					"Triangle inequality violated: distance(A->C) = %.2f > distance(A->B) + distance(B->C) = %.2f + %.2f = %.2f\n" .
					"Point A: (%.6f, %.6f)\n" .
					"Point B: (%.6f, %.6f)\n" .
					"Point C: (%.6f, %.6f)",
					$distanceAC,
					$distanceAB,
					$distanceBC,
					$distanceAB + $distanceBC,
					$latA,
					$lonA,
					$latB,
					$lonB,
					$latC,
					$lonC,
				),
			);
		});
	}
}
