<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Property-based tests for method return type completeness.
 * These tests verify that all methods have explicit return types.
 */
final class MethodReturnTypesPropertyTest extends TestCase
{
	/**
	 * **Feature: php-8-4-migration, Property 6: Method return type completeness**
	 * **Validates: Requirements 5.1, 5.2, 5.3, 5.4**
	 *
	 * For any method in the codebase, the method SHALL have an explicit return type declaration.
	 */
	public function test_property_all_helper_methods_have_return_types(): void
	{
		$helperClasses = [
			\phpGPX\Helpers\GeoHelper::class,
			\phpGPX\Helpers\DateTimeHelper::class,
			\phpGPX\Helpers\SerializationHelper::class,
			\phpGPX\Helpers\DistanceCalculator::class,
			\phpGPX\Helpers\ElevationGainLossCalculator::class,
			\phpGPX\Helpers\BoundsCalculator::class,
		];

		foreach ($helperClasses as $className) {
			$this->assertAllMethodsHaveReturnTypes($className);
		}
	}

	/**
	 * Assert that all methods in a class have explicit return types.
	 */
	private function assertAllMethodsHaveReturnTypes(string $className): void
	{
		$reflection = new ReflectionClass($className);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE);

		foreach ($methods as $method) {
			// Skip constructor
			if ($method->getName() === '__construct') {
				continue;
			}

			// Skip methods inherited from parent classes (like TestCase methods)
			if ($method->getDeclaringClass()->getName() !== $className) {
				continue;
			}

			$hasReturnType = $method->hasReturnType();
			$this->assertTrue(
				$hasReturnType,
				sprintf(
					"Method %s::%s() does not have an explicit return type",
					$className,
					$method->getName(),
				),
			);
		}
	}
}
