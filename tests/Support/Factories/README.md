# Test Factories

This directory contains factory classes for creating test data objects.

## Available Factories

### PointFactory

Creates `Point` instances for testing.

**Methods:**

```php
// Create point with default values
$point = PointFactory::create();

// Create point with custom values
$point = PointFactory::create([
    'latitude' => 50.0,
    'longitude' => 10.0,
    'elevation' => 100.0,
    'name' => 'Test Point',
]);

// Create point with elevation
$point = PointFactory::createWithElevation(123.45);

// Create point at specific coordinates
$point = PointFactory::createAtCoordinates(45.0, 15.0);

// Create sequence of points along a path
$points = PointFactory::createSequence(
    count: 10,
    startLat: 54.0,
    startLon: 9.0,
    increment: 0.01
);
```

### SegmentFactory

Creates `Segment` instances for testing.

**Methods:**

```php
// Create empty segment
$segment = SegmentFactory::create();

// Create segment with custom points
$segment = SegmentFactory::create([
    'points' => [$point1, $point2, $point3]
]);

// Create segment with N points
$segment = SegmentFactory::createWithPoints(10);
```

### TrackFactory

Creates `Track` instances for testing.

**Methods:**

```php
// Create empty track
$track = TrackFactory::create();

// Create track with custom values
$track = TrackFactory::create([
    'name' => 'My Track',
    'description' => 'Test track',
    'segments' => [$segment1, $segment2]
]);

// Create track with N points in single segment
$track = TrackFactory::createWithPoints(15);

// Create track with N segments, each with M points
$track = TrackFactory::createWithSegments(
    segmentCount: 3,
    pointsPerSegment: 5
);
```

### MetadataFactory

Creates `Metadata` instances for testing.

**Methods:**

```php
// Create metadata with default values
$metadata = MetadataFactory::create();

// Create metadata with custom values
$metadata = MetadataFactory::create([
    'name' => 'My GPX',
    'description' => 'Test file',
    'time' => new \DateTime('2024-01-01'),
]);

// Create metadata with author
$metadata = MetadataFactory::createWithAuthor('John Doe');

// Create metadata with links
$metadata = MetadataFactory::createWithLinks([
    'https://example.com',
    'https://test.com'
]);
```

## Usage Examples

### Simple Test

```php
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

class PointTest extends TestCase
{
    public function test_point_has_coordinates(): void
    {
        $point = PointFactory::create();
        
        $this->assertIsFloat($point->latitude);
        $this->assertIsFloat($point->longitude);
    }
}
```

### Complex Test with Multiple Factories

```php
use phpGPX\Tests\Support\Factories\{
    TrackFactory,
    SegmentFactory,
    PointFactory,
    MetadataFactory
};

class GpxFileTest extends TestCase
{
    public function test_gpx_file_with_complete_data(): void
    {
        $gpxFile = new GpxFile();
        $gpxFile->metadata = MetadataFactory::createWithAuthor('Test Author');
        $gpxFile->tracks[] = TrackFactory::createWithSegments(2, 5);
        $gpxFile->waypoints = PointFactory::createSequence(3);
        
        $this->assertCount(1, $gpxFile->tracks);
        $this->assertCount(2, $gpxFile->tracks[0]->segments);
        $this->assertCount(3, $gpxFile->waypoints);
    }
}
```

### Property-Based Testing

```php
use Eris\Generator;
use phpGPX\Tests\Support\Factories\PointFactory;

class DistanceTest extends TestCase
{
    public function test_distance_is_always_non_negative(): void
    {
        $this->forAll(
            Generator\choose(0, 100)
        )->then(function (int $count) {
            $points = PointFactory::createSequence($count);
            $distance = calculateTotalDistance($points);
            
            $this->assertGreaterThanOrEqual(0, $distance);
        });
    }
}
```

## Design Principles

### Sensible Defaults

All factories provide sensible default values that create valid objects:

```php
// This creates a valid point without any configuration
$point = PointFactory::create();
```

### Easy Customization

Override any property using the `$overrides` array:

```php
$point = PointFactory::create([
    'latitude' => 45.0,
    'elevation' => 100.0,
    // longitude uses default value
]);
```

### Convenience Methods

Common patterns have dedicated methods:

```php
// Instead of:
$point = PointFactory::create(['elevation' => 123.45]);

// Use:
$point = PointFactory::createWithElevation(123.45);
```

### Composability

Factories work together to build complex structures:

```php
// TrackFactory uses SegmentFactory
// SegmentFactory uses PointFactory
$track = TrackFactory::createWithSegments(3, 5);
// Creates track with 3 segments, each with 5 points
```

## Adding New Factories

When creating new factories:

1. Extend the pattern used by existing factories
2. Provide a `create(array $overrides = [])` method
3. Add convenience methods for common use cases
4. Document all methods in this README
5. Add tests to `FactoriesTest.php`

## Requirements Coverage

These factories satisfy Requirements 6.2 from the test coverage spec:
- ✅ Factory methods for creating valid test objects
- ✅ Easy creation of edge case data
- ✅ Support for various test scenarios
