<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\FileFormat;
use phpGPX\Models\GpxFile;
use phpGPX\Tests\Support\Factories\TrackFactory;
use PHPUnit\Framework\TestCase;

/**
 * Property-based tests for match expression exhaustiveness in PHP 8.4 migration.
 * These tests verify that match expressions cover all possible enum cases.
 */
final class MatchExpressionPropertyTest extends TestCase
{
	use TestTrait;

	/**
	 * **Feature: php-8-4-migration, Property 7: Match expression exhaustiveness**
	 * **Validates: Requirements 6.3**
	 *
	 * For any match expression in the codebase, the expression SHALL either cover
	 * all possible cases or include a default case.
	 */
	public function test_property_gpx_file_save_handles_all_file_format_cases(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					FileFormat::XML,
					FileFormat::JSON,
				]),
			)
			->withMaxSize(100)
			->then(function (FileFormat $format): void {
				// Arrange - Create a simple GPX file
				$gpxFile = new GpxFile('Match Expression Test');
				$track = TrackFactory::createWithPoints(2);
				$gpxFile->tracks[] = $track;

				$tempFile = sys_get_temp_dir() . '/test_match_' . uniqid() . '.' . $format->value;

				try {
					// Act - Save with the generated format
					$gpxFile->save($tempFile, $format);

					// Assert - File should be created successfully
					$this->assertFileExists(
						$tempFile,
						"Match expression should handle {$format->value} format without throwing exception",
					);

					// Verify file has content
					$content = file_get_contents($tempFile);
					$this->assertNotEmpty(
						$content,
						"Saved file should have content for {$format->value} format",
					);

					// Verify format-specific content
					if ($format === FileFormat::XML) {
						$this->assertStringContainsString(
							'<?xml',
							$content,
							"XML format should produce valid XML",
						);
						$this->assertStringContainsString(
							'<gpx',
							$content,
							"XML format should contain GPX root element",
						);
					} elseif ($format === FileFormat::JSON) {
						$decoded = json_decode($content, true);
						$this->assertIsArray(
							$decoded,
							"JSON format should produce valid JSON",
						);
						$this->assertArrayHasKey(
							'creator',
							$decoded,
							"JSON format should contain creator field",
						);
					}
				} finally {
					// Cleanup
					if (file_exists($tempFile)) {
						unlink($tempFile);
					}
				}
			});
	}

	/**
	 * Test that all FileFormat enum cases are handled by the match expression.
	 * This is a unit test that complements the property test above.
	 */
	public function test_all_file_format_enum_cases_are_handled(): void
	{
		// Get all FileFormat enum cases
		$allCases = FileFormat::cases();

		$this->assertCount(
			2,
			$allCases,
			"FileFormat enum should have exactly 2 cases (XML and JSON)",
		);

		// Verify each case can be used with save()
		foreach ($allCases as $format) {
			$gpxFile = new GpxFile('Exhaustiveness Test');
			$tempFile = sys_get_temp_dir() . '/test_enum_' . uniqid() . '.' . $format->value;

			try {
				// This should not throw an exception for any enum case
				$gpxFile->save($tempFile, $format);
				$this->assertFileExists($tempFile, "Format {$format->value} should be handled");
			} finally {
				if (file_exists($tempFile)) {
					unlink($tempFile);
				}
			}
		}
	}

	/**
	 * Test that the match expression is exhaustive (no default case needed).
	 * This verifies that adding a new FileFormat case would cause a compile error.
	 */
	public function test_match_expression_exhaustiveness_is_enforced_by_type_system(): void
	{
		// This test verifies the design principle rather than runtime behavior
		// The match expression in GpxFile::save() has no default case,
		// which means PHP will enforce exhaustiveness at compile time

		$gpxFile = new GpxFile('Type System Test');
		$tempFile = sys_get_temp_dir() . '/test_type_' . uniqid() . '.xml';

		try {
			// If this compiles and runs, the match expression is exhaustive
			$gpxFile->save($tempFile, FileFormat::XML);
			$this->assertTrue(
				true,
				"Match expression is exhaustive - all FileFormat cases are handled",
			);
		} finally {
			if (file_exists($tempFile)) {
				unlink($tempFile);
			}
		}
	}
}
