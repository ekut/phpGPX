# Changelog

## 2.0.0 : TBD

**BREAKING CHANGES - GPX 1.1 Schema Compliance**

This major release brings full compliance with the official GPX 1.1 specification from topografix.com. The library now enforces schema requirements at construction time, preventing creation of invalid GPX files.

### Breaking Changes

#### Constructor Signatures Changed

All models with required attributes now enforce them as mandatory constructor parameters:

**Point (Waypoint, TrackPoint, RoutePoint):**
- ❌ Old: `new Point(Point::WAYPOINT)` - coordinates were optional
- ✅ New: `new Point(PointType::WAYPOINT, 54.93, 9.86)` - latitude and longitude required

**Bounds:**
- ❌ Old: `new Bounds(null, null, null, null)` - all coordinates were optional
- ✅ New: `new Bounds(54.0, 9.0, 55.0, 10.0)` - all four coordinates required (minLat, minLon, maxLat, maxLon)

**Link:**
- ❌ Old: `new Link()` or `new Link('')` - href was optional
- ✅ New: `new Link('https://example.com')` - href required and must be non-empty

**Copyright:**
- ❌ Old: `new Copyright()` or `new Copyright('')` - author was optional
- ✅ New: `new Copyright('Author Name')` - author required and must be non-empty

**Email:**
- ❌ Old: `new Email()` or `new Email('', '')` - id and domain were optional
- ✅ New: `new Email('user', 'example.com')` - both id and domain required and must be non-empty

**GpxFile:**
- ❌ Old: `new GpxFile()` - creator was optional
- ✅ New: `new GpxFile('My GPS App v1.0')` - creator required and must be non-empty

### Validation Rules Added

The library now validates all GPX 1.1 schema constraints:

**Coordinate Validation:**
- Latitude must be between -90.0 and 90.0 degrees (WGS84 datum)
- Longitude must be between -180.0 (inclusive) and 180.0 (exclusive) degrees (WGS84 datum)
- Bounds must satisfy logical consistency: minLat ≤ maxLat and minLon ≤ maxLon

**Point Optional Fields:**
- Magnetic variation (magVar) must be between 0.0 (inclusive) and 360.0 (exclusive) degrees
- DGPS station ID (dgpsId) must be between 0 and 1023 (inclusive)
- Satellite count (sat) must be non-negative (≥ 0)

**GPS Fix Type:**
- Fix type now uses `GpsFixType` enum with values: NONE, TWO_D, THREE_D, DGPS, PPS
- Serializes to GPX as: none, 2d, 3d, dgps, pps

**Required Attributes:**
- All required string attributes (href, author, creator, email id/domain) must be non-empty
- Whitespace-only strings are rejected

**Version Enforcement:**
- GPX version "1.1" is always serialized in output files

### Error Messages

All validation failures now throw `InvalidArgumentException` with descriptive messages including:
- The constraint that was violated
- The valid range or requirement
- The invalid value that was provided
- Reference to GPX 1.1 schema where applicable

Example error messages:
- "Latitude must be between -90.0 and 90.0 degrees (WGS84 datum). Got: 95.5"
- "DGPS station ID must be between 0 and 1023 (inclusive). Got: 1500"
- "Link href attribute is required and cannot be empty (GPX 1.1 schema)"

### New Features

- **Added**: `GpxValidator` helper class with reusable validation methods
- **Added**: Validation for all GPX 1.1 constrained value types
- **Added**: Comprehensive property-based tests (31 properties) using Eris
- **Added**: XSD validation tests against official GPX 1.1 schema
- **Added**: Factory methods for generating valid test data

### Benefits

- ✅ Only valid GPX 1.1 files can be created
- ✅ Errors caught at construction time (fail fast)
- ✅ Better IDE autocomplete with required parameters
- ✅ Generated GPX validates against official XSD
- ✅ Clear error messages for debugging

### Migration Guide

See README.md for detailed migration instructions and examples.

### Testing

- Test coverage maintained at 97%
- All 31 correctness properties verified with property-based testing (100+ iterations each)
- Integration tests verify round-trip consistency
- XSD validation confirms schema compliance

## 1.3.0 : 2023-07-19

Changed minimal PHP version to `^7.1` in `composer.json`. Library still should work with PHP5.5+, if you have troubles
while installing check the `--ignore-platform-reqs` attribute of [compose](https://getcomposer.org/doc/03-cli.md).

- **Added**: [Coordinates for remarqued statistic points](https://github.com/Sibyx/phpGPX/pull/64) (minAltitude, maxAltitude, startedAt, finishedAt)

## 1.2.1 : 2022-07-30

- **Fixed**: Fixed `VERSION` string in `phpGPX.php`

## 1.2.0 : 2022-07-30

- **Changed**: [Real distance calculation #37](https://github.com/Sibyx/phpGPX/issues/37) (DistanceCalculator refactor)

## 1.1.3 : 2021-07-29

- **Fixed**: [Fix negative duration #58](https://github.com/Sibyx/phpGPX/pull/58) by [@neronmoon](https://github.com/neronmoon)

## 1.1.2 : 2021-02-28

- **Fixed**: [do SORT_BY_TIMESTAMP only for tracks with timestamps #52](https://github.com/Sibyx/phpGPX/pull/52)

## 1.1.1 : 2021-02-15

- **Fixed**: Fixed `VERSION` string in `phpGPX.php`

## 1.1.0 : 2021-02-05

- **Added**: [Limiting maximum elevation difference to protect from spikes](https://github.com/Sibyx/phpGPX/pull/49) 

## 1.0.1

- **Fixed**: Fixed PersonParser::toXML() if there are no links provided 
  [Error when $person->links is null #48](https://github.com/Sibyx/phpGPX/issues/48)

## 1.0

I am not very proud of idea having a major release in such terrible state. This release is just freeze from 2017 
compatible API and behaviour with some bugfixies. It looks like some people use the library and I want to perform some
radical refactoring. See you in `2.x`. 

- **Fixed**: Do not return extra `:` while parsing unsupported extensions if there is no namespace for child element
- **Fixed**: Fixed Copyright test

## 1.0-RC5

- **Changed:** Moved PHPUnit to development dependencies. 

## 1.0-RC4

 - **Changed:** [Change the way to deal with extensions ](https://github.com/Sibyx/phpGPX/pull/19) 
 - **Fixed:** [RoutePoints and TripExtensions WIP](https://github.com/Sibyx/phpGPX/issues/22)
 - **Fixed:** [Route point rtep versus rtept](https://github.com/Sibyx/phpGPX/issues/21)
 - **Fixed:** [Empty array on load route](https://github.com/Sibyx/phpGPX/issues/20)
 - **Fixed:** Do not load zero altitude in statistics as NULL

## 1.0-RC3

 - **Added:** [Cumulative Elevation in stats](https://github.com/Sibyx/phpGPX/pull/12) with pull request #12 by @Shaydu
 - **Fixed:** [Fix for unterminated entity references](https://github.com/Sibyx/phpGPX/pull/13) with #13 by @benlumley 
 - **Fixed:** [split loading and parsing in separate methods so a string may be loaded as gpx data](https://github.com/Sibyx/phpGPX/pull/9) with #9 by @lommes 
 - **Fixed:** HeartRate [typo that lead to error](https://github.com/Sibyx/phpGPX/issues/14)
 - **Fixed:** Skipping RC2 in packagist [Missing version in packagist](https://github.com/Sibyx/phpGPX/issues/10) 

## 1.0-RC2

 - **Fixed:** [waypoints not loaded correctly - they are ignored](https://github.com/Sibyx/phpGPX/issues/6)
 - Init of unit tests

## 1.0-RC1

Initial release
