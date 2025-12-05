<?php
/**
 * BoundsCalculatorTest.php
 *
 * Unit tests for BoundsCalculator helper class.
 */

namespace UnitTests\phpGPX\Helpers;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Helpers\BoundsCalculator;
use phpGPX\Models\Point;
use PHPUnit\Framework\TestCase;

class BoundsCalculatorTest extends TestCase
{
	use TestTrait;
	/**
	 * Test bounds calculation with a simple set of points.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_simple_point_set_returns_correct_boundaries(): void
	{
		// Create points forming a rectangle
		$point1 = new Point(Point::WAYPOINT, 10.0, 20.0);
		$point2 = new Point(Point::WAYPOINT, 30.0, 40.0);
		$point3 = new Point(Point::WAYPOINT, 15.0, 25.0);

		$points = [$point1, $point2, $point3];

		$bounds = BoundsCalculator::calculate($points);

		// Expected: northwest corner (30, 20) and southeast corner (10, 40)
		$this->assertCount(2, $bounds, "Bounds should return exactly 2 points");
		
		// Northwest corner (highest lat, lowest lon)
		$this->assertEquals(30.0, $bounds[0]['lat'], "Northwest latitude should be 30.0");
		$this->assertEquals(20.0, $bounds[0]['lng'], "Northwest longitude should be 20.0");
		
		// Southeast corner (lowest lat, highest lon)
		$this->assertEquals(10.0, $bounds[1]['lat'], "Southeast latitude should be 10.0");
		$this->assertEquals(40.0, $bounds[1]['lng'], "Southeast longitude should be 40.0");
	}

	/**
	 * Test bounds calculation with a single point.
	 * The bounds should be the same point for both corners.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_single_point_returns_same_point_for_both_corners(): void
	{
		$point = new Point(Point::WAYPOINT, 45.5, -73.6);

		$points = [$point];

		$bounds = BoundsCalculator::calculate($points);

		$this->assertCount(2, $bounds, "Bounds should return exactly 2 points");
		
		// Both corners should be the same point
		$this->assertEquals(45.5, $bounds[0]['lat'], "Northwest latitude should match point latitude");
		$this->assertEquals(-73.6, $bounds[0]['lng'], "Northwest longitude should match point longitude");
		$this->assertEquals(45.5, $bounds[1]['lat'], "Southeast latitude should match point latitude");
		$this->assertEquals(-73.6, $bounds[1]['lng'], "Southeast longitude should match point longitude");
	}

	/**
	 * Test bounds calculation with points spanning multiple hemispheres.
	 * Tests points in all four quadrants (NE, NW, SE, SW).
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_points_spanning_hemispheres_returns_correct_boundaries(): void
	{
		// Northeast quadrant
		$pointNE = new Point(Point::WAYPOINT, 40.0, 50.0);
		// Northwest quadrant
		$pointNW = new Point(Point::WAYPOINT, 35.0, -60.0);
		// Southeast quadrant
		$pointSE = new Point(Point::WAYPOINT, -25.0, 70.0);
		// Southwest quadrant
		$pointSW = new Point(Point::WAYPOINT, -30.0, -80.0);

		$points = [$pointNE, $pointNW, $pointSE, $pointSW];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (40.0), lowest lon (-80.0)
		$this->assertEquals(40.0, $bounds[0]['lat'], "Northwest latitude should be 40.0");
		$this->assertEquals(-80.0, $bounds[0]['lng'], "Northwest longitude should be -80.0");
		
		// Southeast corner: lowest lat (-30.0), highest lon (70.0)
		$this->assertEquals(-30.0, $bounds[1]['lat'], "Southeast latitude should be -30.0");
		$this->assertEquals(70.0, $bounds[1]['lng'], "Southeast longitude should be 70.0");
	}

	/**
	 * Test bounds calculation with points crossing the equator.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_points_crossing_equator_returns_correct_boundaries(): void
	{
		// Point north of equator
		$pointNorth = new Point(Point::WAYPOINT, 15.0, 0.0);
		// Point south of equator
		$pointSouth = new Point(Point::WAYPOINT, -20.0, 10.0);
		// Point on equator
		$pointEquator = new Point(Point::WAYPOINT, 0.0, 5.0);

		$points = [$pointNorth, $pointSouth, $pointEquator];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (15.0), lowest lon (0.0)
		$this->assertEquals(15.0, $bounds[0]['lat'], "Northwest latitude should be 15.0");
		$this->assertEquals(0.0, $bounds[0]['lng'], "Northwest longitude should be 0.0");
		
		// Southeast corner: lowest lat (-20.0), highest lon (10.0)
		$this->assertEquals(-20.0, $bounds[1]['lat'], "Southeast latitude should be -20.0");
		$this->assertEquals(10.0, $bounds[1]['lng'], "Southeast longitude should be 10.0");
	}

	/**
	 * Test bounds calculation with points crossing the prime meridian.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_points_crossing_prime_meridian_returns_correct_boundaries(): void
	{
		// Point west of prime meridian
		$pointWest = new Point(Point::WAYPOINT, 51.5, -5.0);
		// Point east of prime meridian
		$pointEast = new Point(Point::WAYPOINT, 48.8, 2.3);
		// Point on prime meridian
		$pointPrime = new Point(Point::WAYPOINT, 50.0, 0.0);

		$points = [$pointWest, $pointEast, $pointPrime];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (51.5), lowest lon (-5.0)
		$this->assertEquals(51.5, $bounds[0]['lat'], "Northwest latitude should be 51.5");
		$this->assertEquals(-5.0, $bounds[0]['lng'], "Northwest longitude should be -5.0");
		
		// Southeast corner: lowest lat (48.8), highest lon (2.3)
		$this->assertEquals(48.8, $bounds[1]['lat'], "Southeast latitude should be 48.8");
		$this->assertEquals(2.3, $bounds[1]['lng'], "Southeast longitude should be 2.3");
	}

	/**
	 * Test bounds calculation with points at extreme latitudes (near poles).
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_points_near_poles_returns_correct_boundaries(): void
	{
		// Point near North Pole
		$pointNorth = new Point(Point::WAYPOINT, 85.0, 45.0);
		// Point near South Pole
		$pointSouth = new Point(Point::WAYPOINT, -80.0, -120.0);
		// Point in middle latitudes
		$pointMiddle = new Point(Point::WAYPOINT, 0.0, 0.0);

		$points = [$pointNorth, $pointSouth, $pointMiddle];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (85.0), lowest lon (-120.0)
		$this->assertEquals(85.0, $bounds[0]['lat'], "Northwest latitude should be 85.0");
		$this->assertEquals(-120.0, $bounds[0]['lng'], "Northwest longitude should be -120.0");
		
		// Southeast corner: lowest lat (-80.0), highest lon (45.0)
		$this->assertEquals(-80.0, $bounds[1]['lat'], "Southeast latitude should be -80.0");
		$this->assertEquals(45.0, $bounds[1]['lng'], "Southeast longitude should be 45.0");
	}

	/**
	 * Test bounds calculation with points at extreme longitudes (near date line).
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_points_near_date_line_returns_correct_boundaries(): void
	{
		// Point west of date line
		$pointWest = new Point(Point::WAYPOINT, 20.0, 175.0);
		// Point east of date line
		$pointEast = new Point(Point::WAYPOINT, 25.0, -170.0);
		// Point in middle
		$pointMiddle = new Point(Point::WAYPOINT, 22.5, 0.0);

		$points = [$pointWest, $pointEast, $pointMiddle];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (25.0), lowest lon (-170.0)
		$this->assertEquals(25.0, $bounds[0]['lat'], "Northwest latitude should be 25.0");
		$this->assertEquals(-170.0, $bounds[0]['lng'], "Northwest longitude should be -170.0");
		
		// Southeast corner: lowest lat (20.0), highest lon (175.0)
		$this->assertEquals(20.0, $bounds[1]['lat'], "Southeast latitude should be 20.0");
		$this->assertEquals(175.0, $bounds[1]['lng'], "Southeast longitude should be 175.0");
	}

	/**
	 * Test bounds calculation with many points to ensure it handles larger datasets.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_many_points_returns_correct_boundaries(): void
	{
		$points = [];
		
		// Create 100 points with varying coordinates
		for ($i = 0; $i < 100; $i++) {
			$lat = -50.0 + ($i * 1.0); // Range from -50 to 49
			$lon = -100.0 + ($i * 2.0); // Range from -100 to 98
			$points[] = new Point(Point::WAYPOINT, $lat, $lon);
		}

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (49.0), lowest lon (-100.0)
		$this->assertEquals(49.0, $bounds[0]['lat'], "Northwest latitude should be 49.0");
		$this->assertEquals(-100.0, $bounds[0]['lng'], "Northwest longitude should be -100.0");
		
		// Southeast corner: lowest lat (-50.0), highest lon (98.0)
		$this->assertEquals(-50.0, $bounds[1]['lat'], "Southeast latitude should be -50.0");
		$this->assertEquals(98.0, $bounds[1]['lng'], "Southeast longitude should be 98.0");
	}

	/**
	 * Test bounds calculation with points having identical latitudes but different longitudes.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_same_latitude_different_longitude_returns_correct_boundaries(): void
	{
		$point1 = new Point(Point::WAYPOINT, 45.0, -10.0);
		$point2 = new Point(Point::WAYPOINT, 45.0, 20.0);
		$point3 = new Point(Point::WAYPOINT, 45.0, 5.0);

		$points = [$point1, $point2, $point3];

		$bounds = BoundsCalculator::calculate($points);

		// All points have same latitude, so both corners should have lat 45.0
		$this->assertEquals(45.0, $bounds[0]['lat'], "Northwest latitude should be 45.0");
		$this->assertEquals(-10.0, $bounds[0]['lng'], "Northwest longitude should be -10.0");
		$this->assertEquals(45.0, $bounds[1]['lat'], "Southeast latitude should be 45.0");
		$this->assertEquals(20.0, $bounds[1]['lng'], "Southeast longitude should be 20.0");
	}

	/**
	 * Test bounds calculation with points having identical longitudes but different latitudes.
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_same_longitude_different_latitude_returns_correct_boundaries(): void
	{
		$point1 = new Point(Point::WAYPOINT, -20.0, 30.0);
		$point2 = new Point(Point::WAYPOINT, 40.0, 30.0);
		$point3 = new Point(Point::WAYPOINT, 10.0, 30.0);

		$points = [$point1, $point2, $point3];

		$bounds = BoundsCalculator::calculate($points);

		// All points have same longitude, so both corners should have lon 30.0
		$this->assertEquals(40.0, $bounds[0]['lat'], "Northwest latitude should be 40.0");
		$this->assertEquals(30.0, $bounds[0]['lng'], "Northwest longitude should be 30.0");
		$this->assertEquals(-20.0, $bounds[1]['lat'], "Southeast latitude should be -20.0");
		$this->assertEquals(30.0, $bounds[1]['lng'], "Southeast longitude should be 30.0");
	}

	/**
	 * Test bounds calculation with real-world coordinates (major cities).
	 * 
	 * _Requirements: 1.6_
	 */
	public function test_calculate_bounds_with_real_world_coordinates_returns_correct_boundaries(): void
	{
		// New York
		$newYork = new Point(Point::WAYPOINT, 40.7128, -74.0060);
		// London
		$london = new Point(Point::WAYPOINT, 51.5074, -0.1278);
		// Tokyo
		$tokyo = new Point(Point::WAYPOINT, 35.6762, 139.6503);
		// Sydney
		$sydney = new Point(Point::WAYPOINT, -33.8688, 151.2093);

		$points = [$newYork, $london, $tokyo, $sydney];

		$bounds = BoundsCalculator::calculate($points);

		// Northwest corner: highest lat (51.5074 - London), lowest lon (-74.0060 - New York)
		$this->assertEquals(51.5074, $bounds[0]['lat'], "Northwest latitude should be 51.5074 (London)");
		$this->assertEquals(-74.0060, $bounds[0]['lng'], "Northwest longitude should be -74.0060 (New York)");
		
		// Southeast corner: lowest lat (-33.8688 - Sydney), highest lon (151.2093 - Sydney)
		$this->assertEquals(-33.8688, $bounds[1]['lat'], "Southeast latitude should be -33.8688 (Sydney)");
		$this->assertEquals(151.2093, $bounds[1]['lng'], "Southeast longitude should be 151.2093 (Sydney)");
	}

	/**
	 * Property test: Bounds contain all points.
	 * 
	 * **Feature: test-coverage, Property 4: Bounds contain all points**
	 * 
	 * For any set of points, the calculated geographic bounds should contain every
	 * point in the set. This means each point's latitude should be within the
	 * min/max latitude bounds, and each point's longitude should be within the
	 * min/max longitude bounds.
	 * 
	 * This is a fundamental invariant of bounds calculation - the bounding box
	 * must encompass all input points.
	 * 
	 * **Validates: Requirements 1.6, 5.5**
	 */
	public function test_property_bounds_contain_all_points(): void
	{
		$this
			->withRand('mt_rand')  // REQUIRED: Initialize random generator
			->forAll(
				// Generate coordinates for 5 points
				Generators::choose(-90, 90),   // Point 1 latitude
				Generators::choose(-180, 180), // Point 1 longitude
				Generators::choose(-90, 90),   // Point 2 latitude
				Generators::choose(-180, 180), // Point 2 longitude
				Generators::choose(-90, 90),   // Point 3 latitude
				Generators::choose(-180, 180), // Point 3 longitude
				Generators::choose(-90, 90),   // Point 4 latitude
				Generators::choose(-180, 180), // Point 4 longitude
				Generators::choose(-90, 90),   // Point 5 latitude
				Generators::choose(-180, 180)  // Point 5 longitude
			)
			->withMaxSize(100) // Run 100 iterations as specified in design
			->then(function ($lat1, $lon1, $lat2, $lon2, $lat3, $lon3, $lat4, $lon4, $lat5, $lon5) {
				// Convert integers to floats with decimal precision
				$coordinates = [
					['lat' => (float) $lat1 + (mt_rand(0, 999999) / 1000000), 'lon' => (float) $lon1 + (mt_rand(0, 999999) / 1000000)],
					['lat' => (float) $lat2 + (mt_rand(0, 999999) / 1000000), 'lon' => (float) $lon2 + (mt_rand(0, 999999) / 1000000)],
					['lat' => (float) $lat3 + (mt_rand(0, 999999) / 1000000), 'lon' => (float) $lon3 + (mt_rand(0, 999999) / 1000000)],
					['lat' => (float) $lat4 + (mt_rand(0, 999999) / 1000000), 'lon' => (float) $lon4 + (mt_rand(0, 999999) / 1000000)],
					['lat' => (float) $lat5 + (mt_rand(0, 999999) / 1000000), 'lon' => (float) $lon5 + (mt_rand(0, 999999) / 1000000)],
				];
				
				// Ensure we stay within valid ranges
				foreach ($coordinates as &$coord) {
					$coord['lat'] = min(90.0, $coord['lat']);
					$coord['lon'] = min(180.0, $coord['lon']);
				}
				unset($coord);
				
				// Create points
				$points = [];
				foreach ($coordinates as $coord) {
					$points[] = new Point(Point::WAYPOINT, $coord['lat'], $coord['lon']);
				}
				
				// Calculate bounds
				$bounds = BoundsCalculator::calculate($points);
				
				// Extract bounds values
				// bounds[0] = northwest corner (max lat, min lon)
				// bounds[1] = southeast corner (min lat, max lon)
				$maxLat = $bounds[0]['lat'];
				$minLon = $bounds[0]['lng'];
				$minLat = $bounds[1]['lat'];
				$maxLon = $bounds[1]['lng'];
				
				// Property: Every point must be within the calculated bounds
				foreach ($points as $index => $point) {
					$lat = $point->latitude;
					$lon = $point->longitude;
					
					// Check latitude is within bounds
					$this->assertGreaterThanOrEqual(
						$minLat,
						$lat,
						sprintf(
							"Point %d latitude (%.6f) is below minimum bound (%.6f)\n" .
							"Bounds: [%.6f, %.6f] x [%.6f, %.6f]\n" .
							"All points: %s",
							$index,
							$lat,
							$minLat,
							$minLat,
							$maxLat,
							$minLon,
							$maxLon,
							json_encode($coordinates)
						)
					);
					
					$this->assertLessThanOrEqual(
						$maxLat,
						$lat,
						sprintf(
							"Point %d latitude (%.6f) is above maximum bound (%.6f)\n" .
							"Bounds: [%.6f, %.6f] x [%.6f, %.6f]\n" .
							"All points: %s",
							$index,
							$lat,
							$maxLat,
							$minLat,
							$maxLat,
							$minLon,
							$maxLon,
							json_encode($coordinates)
						)
					);
					
					// Check longitude is within bounds
					$this->assertGreaterThanOrEqual(
						$minLon,
						$lon,
						sprintf(
							"Point %d longitude (%.6f) is below minimum bound (%.6f)\n" .
							"Bounds: [%.6f, %.6f] x [%.6f, %.6f]\n" .
							"All points: %s",
							$index,
							$lon,
							$minLon,
							$minLat,
							$maxLat,
							$minLon,
							$maxLon,
							json_encode($coordinates)
						)
					);
					
					$this->assertLessThanOrEqual(
						$maxLon,
						$lon,
						sprintf(
							"Point %d longitude (%.6f) is above maximum bound (%.6f)\n" .
							"Bounds: [%.6f, %.6f] x [%.6f, %.6f]\n" .
							"All points: %s",
							$index,
							$lon,
							$maxLon,
							$minLat,
							$maxLat,
							$minLon,
							$maxLon,
							json_encode($coordinates)
						)
					);
				}
			});
	}
}
