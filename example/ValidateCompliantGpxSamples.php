<?php

declare(strict_types=1);

/**
 * Validate generated GPX sample files against the official GPX 1.1 XSD schema.
 *
 * This script demonstrates that all generated GPX files are fully compliant
 * with the GPX 1.1 specification.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use phpGPX\Tests\Support\XsdValidator;

$samplesDir = __DIR__ . '/gpx-samples';

if (!is_dir($samplesDir)) {
	echo "❌ Error: Sample directory not found. Please run GenerateCompliantGpxSamples.php first.\n";
	exit(1);
}

echo "Validating GPX 1.1 compliant sample files against official XSD schema...\n\n";

$files = glob($samplesDir . '/*.gpx');

if (empty($files)) {
	echo "❌ Error: No GPX files found in samples directory.\n";
	exit(1);
}

$allValid = true;
$validCount = 0;
$totalCount = count($files);

foreach ($files as $file) {
	$filename = basename($file);
	echo "Validating: {$filename}... ";

	$xml = file_get_contents($file);
	$result = XsdValidator::validate($xml);

	if ($result['valid']) {
		echo "✅ VALID\n";
		$validCount++;
	} else {
		echo "❌ INVALID\n";
		echo "Errors:\n";
		foreach ($result['errors'] as $error) {
			echo "  - {$error}\n";
		}
		$allValid = false;
	}
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "Validation Summary:\n";
echo "  Total files: {$totalCount}\n";
echo "  Valid files: {$validCount}\n";
echo "  Invalid files: " . ($totalCount - $validCount) . "\n";
echo str_repeat('=', 70) . "\n\n";

if ($allValid) {
	echo "✅ SUCCESS: All generated GPX files are fully compliant with GPX 1.1 specification!\n";
	echo "\nThis confirms that the phpGPX library generates 100% schema-compliant GPX files.\n";
	exit(0);
} else {
	echo "❌ FAILURE: Some GPX files failed validation.\n";
	exit(1);
}
