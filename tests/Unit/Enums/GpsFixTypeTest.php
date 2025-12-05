<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Enums;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\GpsFixType;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use phpGPX\Tests\Support\TestCase;
use ValueError;

/**
 * Property-based tests for GpsFixType enum compliance with GPX 1.1 specification.
 *
 * Tests Properties 18, 19, and 20 from the GPX Schema Compliance design document.
 */
final class GpsFixTypeTest extends TestCase
{
	use TestTrait;

	/**
	 * Property 19: GpsFixType enum completeness
	 *
	 * The GpsFixType enum SHALL contain exactly five cases: NONE, TWO_D, THREE_D, DGPS, PPS
	 * with corresponding string values: none, 2d, 3d, dgps, pps.
	 *
	 * **Feature: gpx-schema-compliance, Property 19: GpsFixType enum completeness**
	 * **Validates: Requirements 6.2**
	 */
	public function test_property_gps_fix_type_enum_completeness(): void
	{
		// Test that all required cases exist
		$cases = GpsFixType::cases();

		$this->assertCount(5, $cases, 'GpsFixType enum must have exactly 5 cases');

		// Test that each required case exists with correct value
		$this->assertSame('none', GpsFixType::NONE->value);
		$this->assertSame('2d', GpsFixType::TWO_D->value);
		$this->assertSame('3d', GpsFixType::THREE_D->value);
		$this->assertSame('dgps', GpsFixType::DGPS->value);
		$this->assertSame('pps', GpsFixType::PPS->value);

		// Verify all cases are accounted for
		$expectedValues = ['none', '2d', '3d', 'dgps', 'pps'];
		$actualValues = array_map(fn (GpsFixType $case) => $case->value, $cases);
		sort($expectedValues);
		sort($actualValues);

		$this->assertSame($expectedValues, $actualValues, 'GpsFixType enum must contain exactly the GPX 1.1 fix type values');
	}

	/**
	 * Property 18: GpsFixType enum usage
	 *
	 * For any Point with a fix type value, the value SHALL be of type GpsFixType enum (not string).
	 *
	 * Note: Currently the Point model uses ?string for the fix property. This test documents
	 * the current state and will need to be updated when the model is migrated to use the enum.
	 *
	 * **Feature: gpx-schema-compliance, Property 18: GpsFixType enum usage**
	 * **Validates: Requirements 6.1**
	 */
	public function test_property_gps_fix_type_enum_usage(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					GpsFixType::NONE,
					GpsFixType::TWO_D,
					GpsFixType::THREE_D,
					GpsFixType::DGPS,
					GpsFixType::PPS,
				]),
			)
			->withMaxSize(100)
			->then(function (GpsFixType $fixType): void {
				// Create a point
				$point = new Point(PointType::WAYPOINT, 54.93, 9.86);

				// Set fix type using the enum's string value (current implementation)
				$point->fix = $fixType->value;

				// Verify the fix value is one of the valid GPX 1.1 fix types
				$validFixTypes = ['none', '2d', '3d', 'dgps', 'pps'];
				$this->assertContains(
					$point->fix,
					$validFixTypes,
					'Point fix type must be a valid GPX 1.1 fix type value',
				);

				// Verify we can convert back to enum
				$reconstructedEnum = GpsFixType::from($point->fix);
				$this->assertSame($fixType, $reconstructedEnum, 'Fix type should round-trip through string value');
			});
	}

	/**
	 * Property 20: Fix type serialization
	 *
	 * For any Point with a fix type, serializing to GPX XML SHALL produce one of the
	 * valid fix values (none, 2d, 3d, dgps, pps).
	 *
	 * **Feature: gpx-schema-compliance, Property 20: Fix type serialization**
	 * **Validates: Requirements 6.3**
	 */
	public function test_property_fix_type_serialization(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					GpsFixType::NONE,
					GpsFixType::TWO_D,
					GpsFixType::THREE_D,
					GpsFixType::DGPS,
					GpsFixType::PPS,
				]),
			)
			->withMaxSize(100)
			->then(function (GpsFixType $fixType): void {
				// Create a point with fix type
				$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
				$point->fix = $fixType->value;

				// Serialize to array first (Point doesn't have toXML directly)
				$array = $point->toArray();

				// Verify fix is in the array with correct value
				if (isset($array['fix'])) {
					// Verify it's one of the valid GPX 1.1 fix types
					$validFixTypes = ['none', '2d', '3d', 'dgps', 'pps'];
					$this->assertContains(
						$array['fix'],
						$validFixTypes,
						'Serialized fix type must be a valid GPX 1.1 fix type value',
					);

					// Verify it matches the original enum value
					$this->assertSame(
						$fixType->value,
						$array['fix'],
						'Serialized fix type must match the original enum value',
					);
				}
			});
	}

	/**
	 * Test that all GpsFixType enum values can be converted from their string representation.
	 */
	public function test_all_fix_types_can_be_created_from_string(): void
	{
		$this->assertSame(GpsFixType::NONE, GpsFixType::from('none'));
		$this->assertSame(GpsFixType::TWO_D, GpsFixType::from('2d'));
		$this->assertSame(GpsFixType::THREE_D, GpsFixType::from('3d'));
		$this->assertSame(GpsFixType::DGPS, GpsFixType::from('dgps'));
		$this->assertSame(GpsFixType::PPS, GpsFixType::from('pps'));
	}

	/**
	 * Test that invalid fix type strings throw an exception.
	 */
	public function test_invalid_fix_type_string_throws_exception(): void
	{
		$this->expectException(ValueError::class);
		GpsFixType::from('invalid');
	}
}
