# Test Infrastructure

This directory contains the test suite for the phpGPX library, organized to achieve >= 80% code coverage.

## Directory Structure

```
tests/
├── Unit/                    # Unit tests
│   ├── Helpers/            # Tests for Helper classes (90% coverage target)
│   ├── Models/             # Tests for Model classes (90% coverage target)
│   └── Parsers/            # Tests for Parser classes (85% coverage target)
├── Integration/            # Integration tests (80% coverage target)
├── Fixtures/               # Test data files (GPX files, XML samples)
├── Support/                # Test infrastructure
│   ├── TestCase.php       # Base test class with helper methods
│   └── Factories/         # Factory classes for creating test data
│       ├── PointFactory.php
│       ├── TrackFactory.php
│       ├── SegmentFactory.php
│       └── MetadataFactory.php
└── README.md              # This file
```

## Base TestCase Class

All tests should extend `phpGPX\Tests\Support\TestCase` which provides:

### Helper Methods

- `getFixturePath(string $filename): string` - Get full path to a fixture file
- `loadFixture(string $filename): string` - Load fixture file contents
- `assertValidGpxXml(string $xml): void` - Assert that XML is valid GPX format
- `assertCoordinatesEqual(float $expected, float $actual, float $delta = 0.0001): void` - Assert coordinates are equal within delta

### Example Usage

```php
<?php

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Tests\Support\TestCase;
use phpGPX\Models\Point;

final class PointTest extends TestCase
{
    public function test_point_can_be_created(): void
    {
        $point = new Point(Point::TRACKPOINT);
        $point->latitude = 54.9328621088893;
        $point->longitude = 9.860624216140083;
        
        $this->assertCoordinatesEqual(54.9328621088893, $point->latitude);
    }
    
    public function test_load_gpx_fixture(): void
    {
        $xml = $this->loadFixture('minimal-gpx.gpx');
        $this->assertValidGpxXml($xml);
    }
}
```

## Factory Classes

Factory classes provide convenient methods for creating test data:

### PointFactory

```php
use phpGPX\Tests\Support\Factories\PointFactory;

// Create with defaults
$point = PointFactory::create();

// Create with overrides
$point = PointFactory::create([
    'latitude' => 50.0,
    'longitude' => 10.0,
    'elevation' => 100.0,
]);

// Create at specific coordinates
$point = PointFactory::createAtCoordinates(50.0, 10.0);

// Create a sequence of points
$points = PointFactory::createSequence(10); // Creates 10 points
```

### TrackFactory

```php
use phpGPX\Tests\Support\Factories\TrackFactory;

// Create track with points
$track = TrackFactory::createWithPoints(5); // 1 segment with 5 points

// Create track with multiple segments
$track = TrackFactory::createWithSegments(3, 4); // 3 segments, 4 points each
```

### SegmentFactory

```php
use phpGPX\Tests\Support\Factories\SegmentFactory;

// Create segment with points
$segment = SegmentFactory::createWithPoints(10);
```

### MetadataFactory

```php
use phpGPX\Tests\Support\Factories\MetadataFactory;

// Create with defaults
$metadata = MetadataFactory::create();

// Create with author
$metadata = MetadataFactory::createWithAuthor('Test Author');

// Create with links
$metadata = MetadataFactory::createWithLinks(['http://example.com']);
```

## Property-Based Testing with Eris

Eris is installed for property-based testing. Tests using Eris must:

1. Extend `\PHPUnit\Framework\TestCase` directly (not the custom TestCase)
2. Use the `\Eris\TestTrait` trait
3. Use `Eris\Generators` for generating test data

### Example Property-Based Test

```php
<?php

namespace phpGPX\Tests\Unit\Helpers;

use Eris\Generators;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;
use phpGPX\Helpers\GeoHelper;

/**
 * Feature: test-coverage, Property 9: Triangle inequality for distances
 * Validates: Requirements 5.2
 */
final class GeoHelperPropertyTest extends TestCase
{
    use TestTrait;

    public function test_triangle_inequality_for_distances(): void
    {
        $this->forAll(
            Generators::choose(-90, 90),  // latitude A
            Generators::choose(-180, 180), // longitude A
            Generators::choose(-90, 90),  // latitude B
            Generators::choose(-180, 180), // longitude B
            Generators::choose(-90, 90),  // latitude C
            Generators::choose(-180, 180)  // longitude C
        )->then(function ($latA, $lonA, $latB, $lonB, $latC, $lonC): void {
            $distAB = GeoHelper::getRawDistance($latA, $lonA, $latB, $lonB);
            $distBC = GeoHelper::getRawDistance($latB, $lonB, $latC, $lonC);
            $distAC = GeoHelper::getRawDistance($latA, $lonA, $latC, $lonC);
            
            // Triangle inequality: distance(A,C) <= distance(A,B) + distance(B,C)
            $this->assertLessThanOrEqual(
                $distAB + $distBC + 0.001, // small epsilon for floating point
                $distAC,
                "Triangle inequality violated"
            );
        });
    }
}
```

### Eris Generators

Common generators available:

- `Generators::int()` - Any integer
- `Generators::choose($min, $max)` - Integer in range
- `Generators::pos()` - Positive integer
- `Generators::neg()` - Negative integer
- `Generators::nat()` - Natural number (>= 0)
- `Generators::string()` - Random string
- `Generators::float()` - Random float
- `Generators::bool()` - Boolean
- `Generators::elements([...])` - Pick from array
- `Generators::tuple(...)` - Tuple of generators
- `Generators::vector($size, $generator)` - Fixed-size array

### Configuring Eris

By default, Eris runs 100 iterations. You can configure this with attributes:

```php
use Eris\Attributes\ErisRepeat;

#[ErisRepeat(200)]
public function test_with_more_iterations(): void
{
    // This test will run 200 iterations
}
```

## Running Tests

```bash
# All tests
composer test

# Unit tests only
composer test:unit

# Integration tests only
composer test:integration

# With coverage
composer test:coverage

# Specific test file
vendor/bin/phpunit tests/Unit/Helpers/GeoHelperTest.php

# Specific test method
vendor/bin/phpunit --filter test_method_name
```

## Coverage Targets

- **Helpers**: >= 90% coverage
- **Models**: >= 90% coverage
- **Parsers**: >= 85% coverage
- **Overall**: >= 80% coverage

## Test Naming Convention

Follow the pattern: `test_methodName_scenario_expectedResult`

Examples:
- `test_calculate_distance_between_two_points_returns_correct_value`
- `test_parse_gpx_file_with_invalid_xml_throws_exception`
- `test_track_with_no_segments_has_zero_distance`

## Test Structure (Arrange-Act-Assert)

```php
public function test_example(): void
{
    // Arrange - Set up test data
    $point = PointFactory::create(['latitude' => 50.0]);
    
    // Act - Execute the code under test
    $result = $point->calculateSomething();
    
    // Assert - Verify the result
    $this->assertEquals($expected, $result);
}
```

## Fixtures

Fixture files are located in `tests/Fixtures/`:

- `minimal-gpx.gpx` - Minimal valid GPX file
- `track-with-extensions.gpx` - GPX with Garmin extensions
- `invalid-gpx.xml` - Malformed GPX for error testing
- `gps-track.gpx` - Real GPS track data
- `route.gpx` - Route with waypoints
- `timezero.gpx` - Edge case with zero timestamps

Add new fixtures as needed for specific test scenarios.
