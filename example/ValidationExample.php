<?php

declare(strict_types=1);
/**
 * Example demonstrating GPX 1.1 schema validation in phpGPX v2.0
 *
 * This example shows how the library enforces GPX 1.1 constraints
 * and provides clear error messages when validation fails.
 */

use phpGPX\Enums\GpsFixType;
use phpGPX\Enums\PointType;
use phpGPX\Models\Bounds;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Link;
use phpGPX\Models\Point;

require_once '../vendor/autoload.php';

echo "=== GPX 1.1 Schema Validation Examples ===\n\n";

// Example 1: Valid Point Creation
echo "1. Creating a valid Point:\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
	echo "   ✅ Success! Point created at ({$point->latitude}, {$point->longitude})\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Error: {$e->getMessage()}\n\n";
}

// Example 2: Invalid Latitude (too high)
echo "2. Attempting to create Point with invalid latitude (95.0):\n";

try {
	$point = new Point(PointType::WAYPOINT, 95.0, 9.86);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 3: Invalid Longitude (>= 180)
echo "3. Attempting to create Point with invalid longitude (180.0):\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 180.0);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 4: Valid Bounds
echo "4. Creating valid Bounds:\n";

try {
	$bounds = new Bounds(54.0, 9.0, 55.0, 10.0);
	echo "   ✅ Success! Bounds created\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Error: {$e->getMessage()}\n\n";
}

// Example 5: Invalid Bounds (minLat > maxLat)
echo "5. Attempting to create Bounds with minLat > maxLat:\n";

try {
	$bounds = new Bounds(55.0, 9.0, 54.0, 10.0);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 6: Valid Link
echo "6. Creating valid Link:\n";

try {
	$link = new Link('https://example.com', 'Example Site');
	echo "   ✅ Success! Link created\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Error: {$e->getMessage()}\n\n";
}

// Example 7: Invalid Link (empty href)
echo "7. Attempting to create Link with empty href:\n";

try {
	$link = new Link('');
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 8: Valid GpxFile
echo "8. Creating valid GpxFile:\n";

try {
	$gpx = new GpxFile('My GPS App v1.0');
	echo "   ✅ Success! GpxFile created with creator: {$gpx->creator}\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Error: {$e->getMessage()}\n\n";
}

// Example 9: Invalid GpxFile (empty creator)
echo "9. Attempting to create GpxFile with empty creator:\n";

try {
	$gpx = new GpxFile('');
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 10: Point with optional field validation
echo "10. Setting optional fields on Point:\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 9.86);

	// Valid magnetic variation
	$point->setMagVar(45.5);
	echo "   ✅ Magnetic variation set to 45.5°\n";

	// Valid DGPS station ID
	$point->setDgpsId(100);
	echo "   ✅ DGPS station ID set to 100\n";

	// Valid satellite count
	$point->setSat(8);
	echo "   ✅ Satellite count set to 8\n";

	// Valid GPS fix type
	$point->fix = GpsFixType::THREE_D;
	echo "   ✅ GPS fix type set to 3D\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Error: {$e->getMessage()}\n\n";
}

// Example 11: Invalid magnetic variation
echo "11. Attempting to set invalid magnetic variation (400.0):\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
	$point->setMagVar(400.0);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 12: Invalid DGPS station ID
echo "12. Attempting to set invalid DGPS station ID (2000):\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
	$point->setDgpsId(2000);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

// Example 13: Invalid satellite count
echo "13. Attempting to set invalid satellite count (-5):\n";

try {
	$point = new Point(PointType::WAYPOINT, 54.93, 9.86);
	$point->setSat(-5);
	echo "   ✅ Success! (This shouldn't happen)\n\n";
} catch (\InvalidArgumentException $e) {
	echo "   ❌ Expected error: {$e->getMessage()}\n\n";
}

echo "=== Validation Examples Complete ===\n";
echo "\nKey Takeaways:\n";
echo "- All required parameters must be provided at construction time\n";
echo "- Coordinates are validated against WGS84 datum ranges\n";
echo "- Optional fields have validation when set\n";
echo "- Clear error messages help identify and fix issues\n";
echo "- Only valid GPX 1.1 files can be created\n";
