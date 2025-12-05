<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support;

use RuntimeException;

/**
 * Test the TestCase base class helper methods.
 */
final class TestCaseTest extends TestCase
{
	public function test_get_fixture_path_returns_correct_path(): void
	{
		$path = $this->getFixturePath('minimal-gpx.gpx');

		$this->assertStringContainsString('minimal-gpx.gpx', $path);
		$this->assertFileExists($path);
	}

	public function test_load_fixture_returns_file_contents(): void
	{
		$contents = $this->loadFixture('minimal-gpx.gpx');

		$this->assertIsString($contents);
		$this->assertStringContainsString('<?xml', $contents);
		$this->assertStringContainsString('<gpx', $contents);
	}

	public function test_load_fixture_throws_exception_for_missing_file(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Fixture file not found');

		$this->loadFixture('non-existent-file.gpx');
	}

	public function test_assert_valid_gpx_xml_passes_for_valid_xml(): void
	{
		$xml = $this->loadFixture('minimal-gpx.gpx');

		// Should not throw an exception
		$this->assertValidGpxXml($xml);

		// If we get here, the assertion passed
		$this->assertTrue(true);
	}

	public function test_assert_valid_gpx_xml_fails_for_invalid_xml(): void
	{
		$xml = $this->loadFixture('invalid-gpx.xml');

		$this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
		$this->expectExceptionMessage('XML is not well-formed');

		$this->assertValidGpxXml($xml);
	}

	public function test_assert_valid_gpx_xml_fails_for_non_gpx_root(): void
	{
		$xml = '<?xml version="1.0"?><root></root>';

		$this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
		$this->expectExceptionMessage('Root element must be <gpx>');

		$this->assertValidGpxXml($xml);
	}

	public function test_assert_coordinates_equal_passes_for_equal_values(): void
	{
		$this->assertCoordinatesEqual(54.9328621088893, 54.9328621088893);

		// If we get here, the assertion passed
		$this->assertTrue(true);
	}

	public function test_assert_coordinates_equal_passes_within_delta(): void
	{
		$this->assertCoordinatesEqual(54.9328621088893, 54.9328621088894, 0.0001);

		// If we get here, the assertion passed
		$this->assertTrue(true);
	}

	public function test_assert_coordinates_equal_fails_outside_delta(): void
	{
		$this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

		$this->assertCoordinatesEqual(54.9328621088893, 54.9428621088893, 0.0001);
	}

	public function test_all_required_fixtures_exist(): void
	{
		$requiredFixtures = [
			'minimal-gpx.gpx',
			'complete-gpx.gpx',
			'track-with-extensions.gpx',
			'invalid-gpx.xml',
			'route.gpx',
		];

		foreach ($requiredFixtures as $fixture) {
			$path = $this->getFixturePath($fixture);
			$this->assertFileExists($path, "Required fixture missing: {$fixture}");
		}
	}
}
