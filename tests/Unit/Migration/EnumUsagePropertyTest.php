<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use PHPUnit\Framework\TestCase;

/**
 * Property-based tests for enum usage consistency in PHP 8.4 migration.
 * These tests verify that enum cases are used instead of string literals.
 */
final class EnumUsagePropertyTest extends TestCase
{
	use TestTrait;

	/**
	 * **Feature: php-8-4-migration, Property 8: Enum usage consistency**
	 * **Validates: Requirements 7.3**
	 *
	 * For any reference to point types, the code SHALL use enum cases rather than string literals.
	 */
	public function test_property_point_constructor_accepts_enum_and_returns_enum(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					PointType::WAYPOINT,
					PointType::TRACKPOINT,
					PointType::ROUTEPOINT,
				]),
				Generators::choose(-90, 90),   // latitude
				Generators::choose(-180, 179), // longitude (must be < 180)
			)
			->withMaxSize(100)
			->then(function (PointType $pointType, int $lat, int $lon): void {
				// Create point with enum and valid coordinates
				$point = new Point($pointType, (float)$lat, (float)$lon);

				// Verify getPointType returns the same enum
				$returnedType = $point->getPointType();

				$this->assertInstanceOf(
					PointType::class,
					$returnedType,
					"getPointType() should return a PointType enum instance",
				);

				$this->assertSame(
					$pointType,
					$returnedType,
					"getPointType() should return the same enum value that was passed to constructor",
				);
			});
	}

	/**
	 * Test that Point constructor maintains backward compatibility with string values.
	 */
	public function test_property_point_constructor_accepts_legacy_strings_and_converts_to_enum(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					['string' => 'waypoint', 'enum' => PointType::WAYPOINT],
					['string' => 'track', 'enum' => PointType::TRACKPOINT],
					['string' => 'route', 'enum' => PointType::ROUTEPOINT],
				]),
				Generators::choose(-90, 90),   // latitude
				Generators::choose(-180, 179), // longitude (must be < 180)
			)
			->withMaxSize(100)
			->then(function (array $testCase, int $lat, int $lon): void {
				// Create point with legacy string and valid coordinates
				$point = new Point($testCase['string'], (float)$lat, (float)$lon);

				// Verify getPointType returns enum
				$returnedType = $point->getPointType();

				$this->assertInstanceOf(
					PointType::class,
					$returnedType,
					"getPointType() should return a PointType enum even when constructed with string",
				);

				$this->assertSame(
					$testCase['enum'],
					$returnedType,
					"getPointType() should return the correct enum for the legacy string value",
				);
			});
	}

	/**
	 * Test that enum values match the expected string representations.
	 */
	public function test_enum_values_match_legacy_constants(): void
	{
		$this->assertSame('waypoint', PointType::WAYPOINT->value);
		$this->assertSame('track', PointType::TRACKPOINT->value);
		$this->assertSame('route', PointType::ROUTEPOINT->value);

		// Verify legacy constants still exist for backward compatibility
		$this->assertSame('waypoint', Point::WAYPOINT);
		$this->assertSame('track', Point::TRACKPOINT);
		$this->assertSame('route', Point::ROUTEPOINT);
	}
}
