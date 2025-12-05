<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Property-based tests for constructor promotion correctness in PHP 8.4 migration.
 * These tests verify that constructor promotion is used correctly without redundant declarations.
 */
final class ConstructorPromotionPropertyTest extends TestCase
{
	/**
	 * **Feature: php-8-4-migration, Property 4: Constructor promotion correctness**
	 * **Validates: Requirements 3.3**
	 *
	 * For any constructor using property promotion, there SHALL NOT exist a separate property declaration.
	 */
	public function test_property_no_redundant_property_declarations_with_constructor_promotion(): void
	{
		$modelsWithConstructorPromotion = [
			\phpGPX\Models\Bounds::class,
			\phpGPX\Models\Email::class,
			\phpGPX\Models\Link::class,
			\phpGPX\Models\Copyright::class,
			\phpGPX\Models\Person::class,
		];

		foreach ($modelsWithConstructorPromotion as $modelClass) {
			$this->assertNoRedundantPropertyDeclarations($modelClass);
		}
	}

	/**
	 * Assert that a class using constructor promotion doesn't have redundant property declarations.
	 */
	private function assertNoRedundantPropertyDeclarations(string $className): void
	{
		$reflection = new ReflectionClass($className);
		$constructor = $reflection->getConstructor();

		if ($constructor === null) {
			return;
		}

		$promotedProperties = [];
		foreach ($constructor->getParameters() as $parameter) {
			if ($parameter->isPromoted()) {
				$promotedProperties[] = $parameter->getName();
			}
		}

		// Get all properties declared in the class (not inherited)
		$declaredProperties = [];
		foreach ($reflection->getProperties() as $property) {
			if ($property->getDeclaringClass()->getName() === $className) {
				$declaredProperties[] = $property->getName();
			}
		}

		// Check for redundant declarations
		foreach ($promotedProperties as $promotedProp) {
			// In PHP 8.0+, promoted properties are automatically declared
			// So we check that there's no explicit declaration in the class body
			// by reading the source code
			$source = file_get_contents($reflection->getFileName());

			// Remove the constructor to avoid false positives
			$constructorStart = strpos($source, 'public function __construct');
			$constructorEnd = strpos($source, '}', $constructorStart);
			$beforeConstructor = substr($source, 0, $constructorStart);
			$afterConstructor = substr($source, $constructorEnd + 1);
			$sourceWithoutConstructor = $beforeConstructor . $afterConstructor;

			// Look for property declarations like "public $propertyName" or "public Type $propertyName"
			$pattern = '/^\s*(public|protected|private)\s+(\??\w+\s+)?\$' . preg_quote($promotedProp, '/') . '\s*[;=]/m';

			$this->assertDoesNotMatchRegularExpression(
				$pattern,
				$sourceWithoutConstructor,
				sprintf(
					"Class %s has redundant property declaration for promoted property \$%s",
					$className,
					$promotedProp,
				),
			);
		}
	}
}
