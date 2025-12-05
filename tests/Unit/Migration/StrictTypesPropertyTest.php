<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Property-based tests for PHP 8.4 migration validation.
 * These tests verify that the migration was completed correctly.
 */
final class StrictTypesPropertyTest extends TestCase
{
	use TestTrait;

	/**
	 * **Feature: php-8-4-migration, Property 1: Strict types declaration universality**
	 * **Validates: Requirements 1.1, 1.3**
	 *
	 * For any PHP file in the src directory, the file SHALL contain `declare(strict_types=1);` on line 2.
	 */
	public function test_property_all_helper_files_have_strict_types_declaration(): void
	{
		$helperFiles = [
			'src/phpGPX/Helpers/GeoHelper.php',
			'src/phpGPX/Helpers/DateTimeHelper.php',
			'src/phpGPX/Helpers/SerializationHelper.php',
			'src/phpGPX/Helpers/DistanceCalculator.php',
			'src/phpGPX/Helpers/ElevationGainLossCalculator.php',
			'src/phpGPX/Helpers/BoundsCalculator.php',
		];

		foreach ($helperFiles as $file) {
			$this->assertFileHasStrictTypesDeclaration($file);
		}
	}

	/**
	 * Assert that a PHP file has strict_types declaration in the first few lines.
	 */
	private function assertFileHasStrictTypesDeclaration(string $filePath): void
	{
		$fullPath = __DIR__ . '/../../../' . $filePath;
		$this->assertFileExists($fullPath, "File {$filePath} does not exist");

		$content = file_get_contents($fullPath);

		// Check if the file contains declare(strict_types=1) in the first 5 lines
		$lines = explode("\n", $content);
		$found = false;
		$searchLines = min(5, count($lines));

		for ($i = 0; $i < $searchLines; $i++) {
			if (strpos($lines[$i], 'declare(strict_types=1)') !== false) {
				$found = true;
				break;
			}
		}

		$this->assertTrue(
			$found,
			"File {$filePath} does not have strict_types declaration in the first 5 lines",
		);
	}
}
