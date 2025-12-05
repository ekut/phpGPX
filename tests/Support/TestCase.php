<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;

/**
 * Base test case class with helper methods for fixtures and assertions.
 */
abstract class TestCase extends PHPUnitTestCase
{
	/**
	 * Get the full path to a fixture file.
	 *
	 * @param string $filename The fixture filename
	 * @return string The full path to the fixture file
	 */
	protected function getFixturePath(string $filename): string
	{
		return __DIR__ . '/../fixtures/' . $filename;
	}

	/**
	 * Load the contents of a fixture file.
	 *
	 * @param string $filename The fixture filename
	 * @return string The contents of the fixture file
	 * @throws RuntimeException If the fixture file cannot be read
	 */
	protected function loadFixture(string $filename): string
	{
		$path = $this->getFixturePath($filename);

		if (!file_exists($path)) {
			throw new RuntimeException("Fixture file not found: {$path}");
		}

		$contents = file_get_contents($path);

		if ($contents === false) {
			throw new RuntimeException("Failed to read fixture file: {$path}");
		}

		return $contents;
	}

	/**
	 * Assert that a string is valid GPX XML.
	 *
	 * @param string $xml The XML string to validate
	 * @return void
	 */
	protected function assertValidGpxXml(string $xml): void
	{
		// Check that it's valid XML
		$previousErrorHandling = libxml_use_internal_errors(true);
		$doc = simplexml_load_string($xml);
		$errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors($previousErrorHandling);

		$this->assertNotFalse($doc, 'XML is not well-formed: ' . $this->formatXmlErrors($errors));

		// Check that it has the GPX root element
		$this->assertEquals('gpx', $doc->getName(), 'Root element must be <gpx>');

		// Check that it has the required namespace
		$namespaces = $doc->getNamespaces(true);
		$this->assertArrayHasKey('', $namespaces, 'GPX namespace is missing');
		$this->assertStringContainsString('topografix.com/GPX', $namespaces[''], 'Invalid GPX namespace');
	}

	/**
	 * Assert that two coordinate values are equal within a delta.
	 *
	 * @param float $expected The expected coordinate value
	 * @param float $actual The actual coordinate value
	 * @param float $delta The maximum difference allowed (default: 0.0001)
	 * @param string $message Optional message
	 * @return void
	 */
	protected function assertCoordinatesEqual(
		float $expected,
		float $actual,
		float $delta = 0.0001,
		string $message = '',
	): void {
		$this->assertEqualsWithDelta($expected, $actual, $delta, $message);
	}

	/**
	 * Format XML errors for display.
	 *
	 * @param array $errors Array of LibXMLError objects
	 * @return string Formatted error messages
	 */
	private function formatXmlErrors(array $errors): string
	{
		if (empty($errors)) {
			return '';
		}

		$messages = [];
		foreach ($errors as $error) {
			$messages[] = sprintf(
				'Line %d: %s',
				$error->line,
				trim($error->message),
			);
		}

		return implode('; ', $messages);
	}
}
