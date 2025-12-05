<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Property-based tests for property type completeness in PHP 8.4 migration.
 * These tests verify that all properties have explicit type declarations.
 */
final class PropertyTypesPropertyTest extends TestCase
{
	/**
	 * **Feature: php-8-4-migration, Property 3: Property type completeness**
	 * **Validates: Requirements 2.1, 2.4**
	 *
	 * For any class property in the codebase, the property SHALL have an explicit type declaration.
	 */
	public function test_property_all_simple_model_properties_have_explicit_types(): void
	{
		$simpleModels = [
			\phpGPX\Models\Bounds::class,
			\phpGPX\Models\Email::class,
			\phpGPX\Models\Link::class,
			\phpGPX\Models\Copyright::class,
			\phpGPX\Models\Person::class,
			\phpGPX\Models\Extensions::class,
			\phpGPX\Models\Extensions\TrackPointExtension::class,
		];

		foreach ($simpleModels as $modelClass) {
			$this->assertAllPropertiesHaveTypes($modelClass);
		}
	}

	/**
	 * Assert that all properties in a class have explicit type declarations.
	 */
	private function assertAllPropertiesHaveTypes(string $className): void
	{
		$reflection = new ReflectionClass($className);
		$properties = $reflection->getProperties();

		foreach ($properties as $property) {
			// Skip static properties and constants
			if ($property->isStatic()) {
				continue;
			}

			$hasType = $property->hasType();

			$this->assertTrue(
				$hasType,
				sprintf(
					"Property %s::\$%s does not have an explicit type declaration",
					$className,
					$property->getName(),
				),
			);
		}
	}
}
