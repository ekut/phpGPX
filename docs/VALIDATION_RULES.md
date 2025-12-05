# GPX 1.1 Validation Rules

This document describes all validation rules enforced by phpGPX v2.0 for GPX 1.1 schema compliance.

## Overview

Version 2.0 enforces all GPX 1.1 specification constraints at construction time. Invalid values are rejected immediately with clear error messages, ensuring only valid GPX files can be created.

## Coordinate Validation

### Latitude

**Range:** -90.0 to 90.0 degrees (inclusive)  
**Datum:** WGS84  
**Applies to:** Point, Bounds

**Valid Examples:**
```php
$point = new Point(PointType::WAYPOINT, -90.0, 0.0);  // South Pole
$point = new Point(PointType::WAYPOINT, 0.0, 0.0);    // Equator
$point = new Point(PointType::WAYPOINT, 90.0, 0.0);   // North Pole
```

**Invalid Examples:**
```php
$point = new Point(PointType::WAYPOINT, -91.0, 0.0);  // ❌ Too far south
$point = new Point(PointType::WAYPOINT, 95.0, 0.0);   // ❌ Too far north
```

**Error Message:**
```
Latitude must be between -90.0 and 90.0 degrees (WGS84 datum). Got: 95.0
```

### Longitude

**Range:** -180.0 (inclusive) to 180.0 (exclusive) degrees  
**Datum:** WGS84  
**Applies to:** Point, Bounds

**Valid Examples:**
```php
$point = new Point(PointType::WAYPOINT, 0.0, -180.0);   // Valid
$point = new Point(PointType::WAYPOINT, 0.0, 0.0);      // Prime Meridian
$point = new Point(PointType::WAYPOINT, 0.0, 179.999);  // Valid
```

**Invalid Examples:**
```php
$point = new Point(PointType::WAYPOINT, 0.0, -181.0);  // ❌ Too far west
$point = new Point(PointType::WAYPOINT, 0.0, 180.0);   // ❌ Exactly 180 is invalid
$point = new Point(PointType::WAYPOINT, 0.0, 200.0);   // ❌ Too far east
```

**Error Message:**
```
Longitude must be between -180.0 (inclusive) and 180.0 (exclusive) degrees (WGS84 datum). Got: 180.0
```

## Bounds Validation

### Required Coordinates

All four coordinates are required per GPX 1.1 schema.

**Valid Example:**
```php
$bounds = new Bounds(
    minLatitude: 54.0,
    minLongitude: 9.0,
    maxLatitude: 55.0,
    maxLongitude: 10.0
);
```

**Invalid Example:**
```php
$bounds = new Bounds(null, null, null, null);  // ❌ TypeError - all required
```

### Logical Consistency

Minimum values must be less than or equal to maximum values.

**Valid Examples:**
```php
$bounds = new Bounds(54.0, 9.0, 55.0, 10.0);   // min < max
$bounds = new Bounds(54.0, 9.0, 54.0, 9.0);    // min == max (single point)
```

**Invalid Examples:**
```php
$bounds = new Bounds(55.0, 9.0, 54.0, 10.0);   // ❌ minLat > maxLat
$bounds = new Bounds(54.0, 10.0, 55.0, 9.0);   // ❌ minLon > maxLon
```

**Error Messages:**
```
Minimum latitude (55.0) cannot be greater than maximum latitude (54.0)
Minimum longitude (10.0) cannot be greater than maximum longitude (9.0)
```

## Point Optional Fields

### Magnetic Variation (magVar)

**Range:** 0.0 (inclusive) to 360.0 (exclusive) degrees  
**Type:** float  
**Optional:** Yes

**Valid Examples:**
```php
$point->setMagVar(0.0);      // Valid
$point->setMagVar(45.5);     // Valid
$point->setMagVar(359.999);  // Valid
$point->setMagVar(null);     // Valid (optional field)
```

**Invalid Examples:**
```php
$point->setMagVar(-1.0);    // ❌ Negative
$point->setMagVar(360.0);   // ❌ Exactly 360 is invalid
$point->setMagVar(400.0);   // ❌ Too large
```

**Error Message:**
```
Degrees must be between 0.0 (inclusive) and 360.0 (exclusive). Got: 400.0
```

### DGPS Station ID (dgpsId)

**Range:** 0 to 1023 (inclusive)  
**Type:** int  
**Optional:** Yes

**Valid Examples:**
```php
$point->setDgpsId(0);      // Valid
$point->setDgpsId(100);    // Valid
$point->setDgpsId(1023);   // Valid
$point->setDgpsId(null);   // Valid (optional field)
```

**Invalid Examples:**
```php
$point->setDgpsId(-1);     // ❌ Negative
$point->setDgpsId(1024);   // ❌ Too large
$point->setDgpsId(2000);   // ❌ Too large
```

**Error Message:**
```
DGPS station ID must be between 0 and 1023 (inclusive). Got: 2000
```

### Satellite Count (sat)

**Range:** >= 0 (non-negative)  
**Type:** int  
**Optional:** Yes

**Valid Examples:**
```php
$point->setSat(0);      // Valid
$point->setSat(8);      // Valid
$point->setSat(12);     // Valid
$point->setSat(null);   // Valid (optional field)
```

**Invalid Examples:**
```php
$point->setSat(-1);    // ❌ Negative
$point->setSat(-5);    // ❌ Negative
```

**Error Message:**
```
Satellite count must be non-negative. Got: -5
```

## GPS Fix Type

**Type:** GpsFixType enum  
**Values:** NONE, TWO_D, THREE_D, DGPS, PPS  
**Serializes as:** none, 2d, 3d, dgps, pps

**Valid Examples:**
```php
use phpGPX\Enums\GpsFixType;

$point->fix = GpsFixType::NONE;
$point->fix = GpsFixType::TWO_D;
$point->fix = GpsFixType::THREE_D;
$point->fix = GpsFixType::DGPS;
$point->fix = GpsFixType::PPS;
$point->fix = null;  // Optional field
```

**Invalid Examples:**
```php
$point->fix = 'invalid';  // ❌ Must use enum
$point->fix = '4d';       // ❌ Not a valid fix type
```

## Required String Attributes

All required string attributes must be non-empty. Whitespace-only strings are rejected.

### Link href

**Required:** Yes  
**Must be:** Non-empty string

**Valid Examples:**
```php
$link = new Link('https://example.com');
$link = new Link('http://example.com/path');
$link = new Link('ftp://example.com');
```

**Invalid Examples:**
```php
$link = new Link('');        // ❌ Empty string
$link = new Link('   ');     // ❌ Whitespace only
$link = new Link("\t\n");    // ❌ Whitespace only
```

**Error Message:**
```
Link href attribute is required and cannot be empty (GPX 1.1 schema)
```

### Copyright author

**Required:** Yes  
**Must be:** Non-empty string

**Valid Examples:**
```php
$copyright = new Copyright('John Doe');
$copyright = new Copyright('Acme Corporation');
```

**Invalid Examples:**
```php
$copyright = new Copyright('');      // ❌ Empty string
$copyright = new Copyright('   ');   // ❌ Whitespace only
```

**Error Message:**
```
Copyright author is required and cannot be empty (GPX 1.1 schema)
```

### Email id and domain

**Required:** Yes (both)  
**Must be:** Non-empty strings

**Valid Examples:**
```php
$email = new Email('user', 'example.com');
$email = new Email('john.doe', 'company.org');
```

**Invalid Examples:**
```php
$email = new Email('', 'example.com');      // ❌ Empty id
$email = new Email('user', '');             // ❌ Empty domain
$email = new Email('   ', 'example.com');   // ❌ Whitespace id
```

**Error Messages:**
```
Email id attribute is required and cannot be empty (GPX 1.1 schema)
Email domain attribute is required and cannot be empty (GPX 1.1 schema)
```

### GpxFile creator

**Required:** Yes  
**Must be:** Non-empty string

**Valid Examples:**
```php
$gpx = new GpxFile('My GPS App v1.0');
$gpx = new GpxFile('Garmin eTrex');
```

**Invalid Examples:**
```php
$gpx = new GpxFile('');      // ❌ Empty string
$gpx = new GpxFile('   ');   // ❌ Whitespace only
```

**Error Message:**
```
GPX creator is required and cannot be empty (GPX 1.1 schema)
```

## Version Enforcement

The GPX version is always serialized as "1.1" in output files. This is enforced automatically and cannot be changed.

**XML Output:**
```xml
<gpx version="1.1" creator="My GPS App v1.0" ...>
```

## Exception Handling

All validation failures throw `\InvalidArgumentException` with descriptive messages that include:

1. **The constraint** that was violated
2. **The valid range** or requirement
3. **The invalid value** that was provided
4. **Reference to GPX 1.1 schema** where applicable

**Example Exception Handling:**
```php
try {
    $point = new Point(PointType::WAYPOINT, 95.0, 9.86);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
    // Output: "Latitude must be between -90.0 and 90.0 degrees (WGS84 datum). Got: 95.0"
    
    // Log the error, show user-friendly message, etc.
}
```

## Validation Helper

All validation logic is centralized in the `GpxValidator` helper class:

```php
use phpGPX\Helpers\GpxValidator;

// Validate coordinates
GpxValidator::validateLatitude(54.93);
GpxValidator::validateLongitude(9.86);

// Validate degrees
GpxValidator::validateDegrees(45.5);

// Validate DGPS station
GpxValidator::validateDgpsStation(100);

// Validate non-negative integer
GpxValidator::validateNonNegativeInteger(8, 'Satellite count');

// Validate non-empty string
GpxValidator::validateNonEmptyString('value', 'Field name');
```

## Testing Validation

To test that your code handles validation correctly:

1. **Unit tests** - Test specific validation scenarios
2. **Integration tests** - Test with real GPX data
3. **XSD validation** - Validate generated GPX against official schema

**Example Test:**
```php
public function test_point_rejects_invalid_latitude(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Latitude must be between -90.0 and 90.0');
    
    new Point(PointType::WAYPOINT, 95.0, 9.86);
}
```

## References

- [GPX 1.1 Specification](http://www.topografix.com/GPX/1/1/)
- [GPX 1.1 XSD Schema](http://www.topografix.com/GPX/1/1/gpx.xsd)
- [WGS84 Coordinate System](https://en.wikipedia.org/wiki/World_Geodetic_System)
