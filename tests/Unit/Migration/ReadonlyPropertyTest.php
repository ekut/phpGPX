<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\PointType;
use phpGPX\Models\Point;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Property-based tests for readonly property enforcement in PHP 8.4 migration.
 * These tests verify that readonly properties cannot be modified after construction.
 */
final class ReadonlyPropertyTest extends TestCase
{
	use TestTrait;

	/**
	 * **Feature: php-8-4-migration, Property 5: Readonly enforcement**
	 * **Validates: Requirements 4.2**
	 * 
	 * For any property declared as readonly, attempting to modify it after construction SHALL throw an Error.
	 */
	public function test_property_point_type_is_readonly_and_cannot_be_modified(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					PointType::WAYPOINT,
					PointType::TRACKPOINT,
					PointType::ROUTEPOINT,
				]),
				Generators::elements([
					PointType::WAYPOINT,
					PointType::TRACKPOINT,
					PointType::ROUTEPOINT,
				])
			)
			->withMaxSize(100)
			->then(function (PointType $initialType, PointType $newType) {
				// Create point with initial type
				$point = new Point($initialType);
				
				// Verify the pointType property is readonly
				$reflection = new ReflectionClass(Point::class);
				$property = $reflection->getProperty('pointType');
				
				$this->assertTrue(
					$property->isReadOnly(),
					"The pointType property should be declared as readonly"
				);
				
				// Attempt to modify the readonly property should throw an Error
				$this->expectException(\Error::class);
				$this->expectExceptionMessageMatches('/Cannot modify readonly property/');
				
				// Use reflection to attempt modification (simulates direct property access)
				$property->setAccessible(true);
				$property->setValue($point, $newType);
			});
	}

	/**
	 * Test that readonly properties are properly identified via reflection.
	 */
	public function test_readonly_properties_are_marked_in_reflection(): void
	{
		$reflection = new ReflectionClass(Point::class);
		$pointTypeProperty = $reflection->getProperty('pointType');
		
		$this->assertTrue(
			$pointTypeProperty->isReadOnly(),
			"The pointType property should be marked as readonly in reflection"
		);
		
		$this->assertTrue(
			$pointTypeProperty->isPrivate(),
			"The pointType property should be private"
		);
	}

	/**
	 * Test that once a Point is constructed, its type cannot change.
	 */
	public function test_point_type_remains_constant_after_construction(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					PointType::WAYPOINT,
					PointType::TRACKPOINT,
					PointType::ROUTEPOINT,
				])
			)
			->withMaxSize(100)
			->then(function (PointType $pointType) {
				$point = new Point($pointType);
				
				// Get the type multiple times
				$type1 = $point->getPointType();
				$type2 = $point->getPointType();
				$type3 = $point->getPointType();
				
				// All should be the same instance
				$this->assertSame($type1, $type2);
				$this->assertSame($type2, $type3);
				$this->assertSame($pointType, $type1);
			});
	}
}
