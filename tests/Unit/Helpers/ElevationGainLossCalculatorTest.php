<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Helpers;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Helpers\ElevationGainLossCalculator;
use phpGPX\Models\Point;
use phpGPX\phpGPX;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for ElevationGainLossCalculator.
 */
final class ElevationGainLossCalculatorTest extends TestCase
{
	use TestTrait;

	/**
	 * Test elevation gain calculation with ascending points.
	 */
	public function test_calculate_elevation_gain_with_ascending_points(): void
	{
		// Arrange - Create points with increasing elevation
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => 150.0]),
			PointFactory::create(['elevation' => 200.0]),
			PointFactory::create(['elevation' => 250.0]),
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Total gain should be 150m (250 - 100), no loss
		$this->assertEquals(150.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test elevation loss calculation with descending points.
	 */
	public function test_calculate_elevation_loss_with_descending_points(): void
	{
		// Arrange - Create points with decreasing elevation
		$points = [
			PointFactory::create(['elevation' => 250.0]),
			PointFactory::create(['elevation' => 200.0]),
			PointFactory::create(['elevation' => 150.0]),
			PointFactory::create(['elevation' => 100.0]),
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Total loss should be 150m (250 - 100), no gain
		$this->assertEquals(0.0, $gain);
		$this->assertEquals(150.0, $loss);
	}

	/**
	 * Test mixed elevation changes (both gain and loss).
	 */
	public function test_calculate_mixed_elevation_changes(): void
	{
		// Arrange - Create points with mixed elevation changes
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => 150.0]), // +50m gain
			PointFactory::create(['elevation' => 120.0]), // -30m loss
			PointFactory::create(['elevation' => 180.0]), // +60m gain
			PointFactory::create(['elevation' => 160.0]), // -20m loss
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Total gain: 50 + 60 = 110m, Total loss: 30 + 20 = 50m
		$this->assertEquals(110.0, $gain);
		$this->assertEquals(50.0, $loss);
	}

	/**
	 * Test handling of null elevation values.
	 */
	public function test_calculate_with_null_elevation_values(): void
	{
		// Arrange - Create points with some null elevations
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => null]), // Should be skipped
			PointFactory::create(['elevation' => 150.0]), // +50m gain from 100
			PointFactory::create(['elevation' => null]), // Should be skipped
			PointFactory::create(['elevation' => 120.0]), // -30m loss from 150
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Null values should be skipped
		$this->assertEquals(50.0, $gain);
		$this->assertEquals(30.0, $loss);
	}

	/**
	 * Test with all null elevation values.
	 */
	public function test_calculate_with_all_null_elevation_values(): void
	{
		// Arrange - Create points with all null elevations
		$points = [
			PointFactory::create(['elevation' => null]),
			PointFactory::create(['elevation' => null]),
			PointFactory::create(['elevation' => null]),
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Should return zero gain and loss
		$this->assertEquals(0.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test with empty points array.
	 */
	public function test_calculate_with_empty_points_array(): void
	{
		// Arrange
		$points = [];

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Assert - Should return zero gain and loss
		$this->assertEquals(0.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test with single point.
	 */
	public function test_calculate_with_single_point(): void
	{
		// Arrange
		$points = [
			PointFactory::create(['elevation' => 100.0]),
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Single point should return zero gain and loss
		$this->assertEquals(0.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test with IGNORE_ELEVATION_0 enabled (default behavior).
	 */
	public function test_calculate_with_ignore_elevation_zero_enabled(): void
	{
		// Arrange - Create points with zero elevation
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => 0.0]), // Should be ignored
			PointFactory::create(['elevation' => 150.0]), // +50m gain from 100
		];

		// Ensure IGNORE_ELEVATION_0 is enabled
		$originalIgnore = phpGPX::$IGNORE_ELEVATION_0;
		phpGPX::$IGNORE_ELEVATION_0 = true;

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original settings
		phpGPX::$IGNORE_ELEVATION_0 = $originalIgnore;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - The algorithm processes points in order:
		// Point 0 (100m): Sets lastConsideredElevation=100, skipped as first point
		// Point 1 (0m): Ignored due to IGNORE_ELEVATION_0 setting
		// Point 2 (150m): Calculates gain from last considered elevation (100m)
		// This results in: gain from 100 to 150 = 50m gain, 0m loss
		$this->assertEquals(50.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test with IGNORE_ELEVATION_0 disabled.
	 */
	public function test_calculate_with_ignore_elevation_zero_disabled(): void
	{
		// Arrange - Create points with zero elevation
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => 0.0]), // Should be considered
			PointFactory::create(['elevation' => 150.0]),
		];

		// Disable IGNORE_ELEVATION_0
		$originalIgnore = phpGPX::$IGNORE_ELEVATION_0;
		phpGPX::$IGNORE_ELEVATION_0 = false;

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original settings
		phpGPX::$IGNORE_ELEVATION_0 = $originalIgnore;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

		// Assert - Zero elevation should be considered
		// Loss: 100 -> 0 = 100m, Gain: 0 -> 150 = 150m
		$this->assertEquals(150.0, $gain);
		$this->assertEquals(100.0, $loss);
	}

	/**
	 * Test with elevation smoothing enabled.
	 */
	public function test_calculate_with_elevation_smoothing_enabled(): void
	{
		// Arrange - Create points with small elevation changes
		$points = [
			PointFactory::create(['elevation' => 100.0]),
			PointFactory::create(['elevation' => 101.0]), // +1m (below threshold)
			PointFactory::create(['elevation' => 105.0]), // +4m (above threshold from 100)
			PointFactory::create(['elevation' => 106.0]), // +1m (below threshold)
			PointFactory::create(['elevation' => 110.0]), // +4m (above threshold from 105)
		];

		// Enable smoothing with threshold of 2m
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		$originalThreshold = phpGPX::$ELEVATION_SMOOTHING_THRESHOLD;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = true;
		phpGPX::$ELEVATION_SMOOTHING_THRESHOLD = 2;

		// Act
		[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

		// Restore original settings
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;
		phpGPX::$ELEVATION_SMOOTHING_THRESHOLD = $originalThreshold;

		// Assert - Only changes > 2m should be counted
		// 100 -> 105 = 5m gain, 105 -> 110 = 5m gain
		$this->assertEquals(10.0, $gain);
		$this->assertEquals(0.0, $loss);
	}

	/**
	 * Test that gain and loss are always non-negative.
	 */
	public function test_gain_and_loss_are_non_negative(): void
	{
		// Arrange - Create various point configurations
		$testCases = [
			[PointFactory::create(['elevation' => 100.0])],
			[
				PointFactory::create(['elevation' => 100.0]),
				PointFactory::create(['elevation' => 200.0]),
			],
			[
				PointFactory::create(['elevation' => 200.0]),
				PointFactory::create(['elevation' => 100.0]),
			],
			[
				PointFactory::create(['elevation' => 100.0]),
				PointFactory::create(['elevation' => 150.0]),
				PointFactory::create(['elevation' => 120.0]),
			],
		];

		// Disable smoothing for predictable results
		$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
		phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

		foreach ($testCases as $points) {
			// Act
			[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

			// Assert - Both gain and loss should be non-negative
			$this->assertGreaterThanOrEqual(0.0, $gain, 'Elevation gain should be non-negative');
			$this->assertGreaterThanOrEqual(0.0, $loss, 'Elevation loss should be non-negative');
		}

		// Restore original setting
		phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;
	}

	/**
	 * Property test: Elevation gain is non-negative.
	 *
	 * **Feature: test-coverage, Property 3: Elevation gain is non-negative**
	 *
	 * For any sequence of points with elevation data, the calculated elevation gain
	 * should always be >= 0 and the calculated elevation loss should always be >= 0.
	 *
	 * This is a fundamental invariant of the elevation calculation - gains and losses
	 * are absolute values and cannot be negative by definition.
	 *
	 * **Validates: Requirements 1.5**
	 */
	public function test_property_elevation_gain_loss_non_negative(): void
	{
		$this
			->withRand('mt_rand')  // REQUIRED: Initialize random generator
			->forAll(
				// Generate elevations for 5 points (simpler and faster)
				Generators::choose(-500, 9000), // Point 1 elevation
				Generators::choose(-500, 9000), // Point 2 elevation
				Generators::choose(-500, 9000), // Point 3 elevation
				Generators::choose(-500, 9000), // Point 4 elevation
				Generators::choose(-500, 9000),  // Point 5 elevation
			)
			->withMaxSize(100) // Run 100 iterations as specified in design
			->then(function ($elev1, $elev2, $elev3, $elev4, $elev5): void {
				// Convert to floats with decimal precision
				$elevations = [
					(float) $elev1 + (mt_rand(0, 999) / 1000.0),
					(float) $elev2 + (mt_rand(0, 999) / 1000.0),
					(float) $elev3 + (mt_rand(0, 999) / 1000.0),
					(float) $elev4 + (mt_rand(0, 999) / 1000.0),
					(float) $elev5 + (mt_rand(0, 999) / 1000.0),
				];

				// Create points with these elevations
				$points = [];
				foreach ($elevations as $elevation) {
					$points[] = PointFactory::create(['elevation' => $elevation]);
				}

				// Save and disable smoothing for predictable results
				$originalSmoothing = phpGPX::$APPLY_ELEVATION_SMOOTHING;
				phpGPX::$APPLY_ELEVATION_SMOOTHING = false;

				// Calculate elevation gain and loss
				[$gain, $loss] = ElevationGainLossCalculator::calculate($points);

				// Restore original setting
				phpGPX::$APPLY_ELEVATION_SMOOTHING = $originalSmoothing;

				// Property: Both gain and loss must be non-negative
				$this->assertGreaterThanOrEqual(
					0.0,
					$gain,
					sprintf(
						"Elevation gain must be non-negative, got: %.2f\n" .
						"Elevations: [%.2f, %.2f, %.2f, %.2f, %.2f]",
						$gain,
						$elevations[0],
						$elevations[1],
						$elevations[2],
						$elevations[3],
						$elevations[4],
					),
				);

				$this->assertGreaterThanOrEqual(
					0.0,
					$loss,
					sprintf(
						"Elevation loss must be non-negative, got: %.2f\n" .
						"Elevations: [%.2f, %.2f, %.2f, %.2f, %.2f]",
						$loss,
						$elevations[0],
						$elevations[1],
						$elevations[2],
						$elevations[3],
						$elevations[4],
					),
				);
			});
	}
}
