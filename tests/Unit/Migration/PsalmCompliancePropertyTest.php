<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;

/**
 * Property test for Psalm level 1 compliance.
 *
 * **Feature: php-8-4-migration, Property 12: Psalm compliance**
 * **Validates: Requirements 11.2**
 */
final class PsalmCompliancePropertyTest extends TestCase
{
	/**
	 * Property 12: Psalm compliance
	 *
	 * For any execution of Psalm at level 1, the analysis SHALL complete with zero issues.
	 *
	 * This test verifies that the codebase passes Psalm level 1 static analysis
	 * without any errors, ensuring type safety and code quality standards are met.
	 */
	public function test_property_psalm_level_1_passes_with_zero_issues(): void
	{
		// Execute Psalm analysis
		$output = [];
		$exitCode = 0;
		exec('composer psalm 2>&1', $output, $exitCode);

		$outputString = implode("\n", $output);

		// Assert that Psalm completed successfully (exit code 0)
		$this->assertSame(
			0,
			$exitCode,
			"Psalm should complete with exit code 0 (no errors).\nOutput:\n{$outputString}",
		);

		// Assert that the output contains the success message
		$this->assertStringContainsString(
			'No errors found!',
			$outputString,
			"Psalm output should contain 'No errors found!' message.\nOutput:\n{$outputString}",
		);

		// Assert that no ERROR section is present in the output
		// Note: Psalm may report "other issues" (info level) which are acceptable
		$this->assertStringNotContainsString(
			'ERROR:',
			$outputString,
			"Psalm output should not contain any ERROR messages.\nOutput:\n{$outputString}",
		);
	}
}
