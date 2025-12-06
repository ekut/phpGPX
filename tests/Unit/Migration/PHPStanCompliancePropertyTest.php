<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;

/**
 * Property test for PHPStan level 8 compliance.
 *
 * **Feature: php-8-4-migration, Property 11: PHPStan compliance**
 * **Validates: Requirements 11.1**
 */
final class PHPStanCompliancePropertyTest extends TestCase
{
	/**
	 * Property 11: PHPStan compliance
	 *
	 * For any execution of PHPStan at level 8, the analysis SHALL complete with zero errors.
	 *
	 * This test verifies that the codebase passes PHPStan level 8 static analysis
	 * without any errors, ensuring type safety and code quality standards are met.
	 */
	public function test_property_phpstan_level_8_passes_with_zero_errors(): void
	{
		// Execute PHPStan analysis
		$output = [];
		$exitCode = 0;
		exec('composer phpstan 2>&1', $output, $exitCode);

		$outputString = implode("\n", $output);

		// Assert that PHPStan completed successfully (exit code 0)
		$this->assertSame(
			0,
			$exitCode,
			"PHPStan should complete with exit code 0 (no errors).\nOutput:\n{$outputString}",
		);

		// Assert that the output contains the success message
		$this->assertStringContainsString(
			'[OK] No errors',
			$outputString,
			"PHPStan output should contain '[OK] No errors' message.\nOutput:\n{$outputString}",
		);

		// Assert that no error count is present in the output
		$this->assertStringNotContainsString(
			'[ERROR]',
			$outputString,
			"PHPStan output should not contain any [ERROR] messages.\nOutput:\n{$outputString}",
		);
	}
}
