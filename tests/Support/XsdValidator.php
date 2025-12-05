<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support;

use DOMDocument;
use LibXMLError;
use RuntimeException;

/**
 * Helper class for validating GPX XML against the official GPX 1.1 XSD schema.
 */
final class XsdValidator
{
	private const XSD_PATH = __DIR__ . '/../fixtures/gpx-1.1.xsd';

	/**
	 * Validate GPX XML string against the GPX 1.1 XSD schema.
	 *
	 * @param string $xmlString The GPX XML to validate
	 * @return array{valid: bool, errors: array<string>} Validation result with errors if any
	 */
	public static function validate(string $xmlString): array
	{
		if (!file_exists(self::XSD_PATH)) {
			throw new RuntimeException(
				'GPX 1.1 XSD schema file not found at: ' . self::XSD_PATH,
			);
		}

		// Enable user error handling
		libxml_use_internal_errors(true);
		libxml_clear_errors();

		$dom = new DOMDocument();
		$dom->loadXML($xmlString);

		$isValid = $dom->schemaValidate(self::XSD_PATH);

		$errors = [];
		if (!$isValid) {
			$xmlErrors = libxml_get_errors();
			foreach ($xmlErrors as $error) {
				$errors[] = self::formatLibXmlError($error);
			}
			libxml_clear_errors();
		}

		return [
			'valid' => $isValid,
			'errors' => $errors,
		];
	}

	/**
	 * Format a libxml error into a readable string.
	 */
	private static function formatLibXmlError(LibXMLError $error): string
	{
		$level = match ($error->level) {
			LIBXML_ERR_WARNING => 'Warning',
			LIBXML_ERR_ERROR => 'Error',
			LIBXML_ERR_FATAL => 'Fatal Error',
			default => 'Unknown',
		};

		return sprintf(
			'%s %d: %s (Line: %d, Column: %d)',
			$level,
			$error->code,
			trim($error->message),
			$error->line,
			$error->column,
		);
	}
}
