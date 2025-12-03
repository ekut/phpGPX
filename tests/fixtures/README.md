# Test Fixtures

This directory contains GPX test fixtures used throughout the test suite.

## Available Fixtures

### Valid GPX Files

#### minimal-gpx.gpx
- **Purpose**: Minimal valid GPX file with only required elements
- **Contents**: 
  - 1 track with 1 segment containing 2 points
  - Basic metadata (name, time)
  - Elevation and time data on track points
- **Use for**: Testing basic parsing, minimal valid structure

#### complete-gpx.gpx
- **Purpose**: Comprehensive GPX file with all optional elements
- **Contents**:
  - 2 waypoints with full details (name, description, symbol, type)
  - 1 route with 3 route points
  - 1 track with 2 segments (3 points each)
  - Complete metadata including:
    - Author with email and link
    - Copyright information
    - Links
    - Keywords
    - Bounds
- **Use for**: Testing full GPX 1.1 specification support, all optional elements

#### track-with-extensions.gpx
- **Purpose**: GPX file with Garmin TrackPointExtension data
- **Contents**:
  - 1 track with 2 points
  - Garmin extensions (heart rate, cadence)
  - Metadata with description
- **Use for**: Testing extension parsing, Garmin-specific data

#### route.gpx
- **Purpose**: GPX file focused on routes
- **Contents**:
  - 2 routes with 4 route points each
  - Route points with names and elevation
- **Use for**: Testing route parsing and handling

#### gps-track.gpx
- **Purpose**: Real-world GPS track data
- **Contents**: Actual GPS track data from real device
- **Use for**: Integration testing with real-world data

#### timezero.gpx
- **Purpose**: GPX file with edge case time values
- **Contents**: Track with specific time-related edge cases
- **Use for**: Testing time/date handling edge cases

### Invalid/Malformed Files

#### invalid-gpx.xml
- **Purpose**: Malformed XML for error handling tests
- **Contents**: GPX with unclosed metadata tag
- **Use for**: Testing error handling, XML validation

## Usage in Tests

### Loading Fixtures

```php
use phpGPX\Tests\Support\TestCase;

class MyTest extends TestCase
{
    public function test_something(): void
    {
        // Get fixture path
        $path = $this->getFixturePath('minimal-gpx.gpx');
        
        // Load fixture contents
        $xml = $this->loadFixture('minimal-gpx.gpx');
        
        // Validate GPX XML
        $this->assertValidGpxXml($xml);
    }
}
```

### Using with phpGPX

```php
use phpGPX\phpGPX;

$gpx = new phpGPX();
$file = $gpx->load('tests/fixtures/complete-gpx.gpx');
```

## Adding New Fixtures

When adding new fixtures:

1. Place the file in this directory
2. Use descriptive filename (e.g., `track-with-multiple-segments.gpx`)
3. Add entry to this README with purpose and contents
4. Ensure valid GPX 1.1 format (unless testing invalid data)
5. Add test case to verify the fixture loads correctly

## Fixture Requirements

According to the test coverage spec (Requirements 6.1-6.4):

- ✅ Common use cases (minimal, complete, extensions, routes)
- ✅ Edge case data (timezero, invalid)
- ✅ Invalid/malformed data (invalid-gpx.xml)
- ✅ Real-world data (gps-track.gpx)
