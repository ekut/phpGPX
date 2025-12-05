# GPX 1.1 Schema Compliance Analysis

## Executive Summary

Analysis of phpGPX models against the official GPX 1.1 specification from topografix.com reveals several **critical discrepancies** that violate the schema requirements.

---

## Critical Issues

### 1. Point Model - Missing Required Attributes ❌

**Schema Requirement (wptType):**
- `latitude` and `longitude` are **XML attributes** (not elements) and are **REQUIRED**
- Type: `latitudeType` (-90.0 ≤ value ≤ 90.0) and `longitudeType` (-180.0 ≤ value < 180.0)

**Current Implementation:**
```php
public ?float $latitude = null;
public ?float $longitude = null;
```

**Problem:** Both properties are nullable, but GPX 1.1 requires them to be mandatory attributes.

**Impact:** 
- Invalid GPX files can be created with missing coordinates
- Tests show points being created without lat/lon (see test examples)
- Violates GPX 1.1 specification

**Recommendation:**
```php
public function __construct(
    PointType|string $pointType,
    public float $latitude,
    public float $longitude
) {
    $this->pointType = $pointType instanceof PointType ? $pointType : PointType::from($pointType);
}
```

---

### 2. Bounds Model - Missing Required Attributes ❌

**Schema Requirement (boundsType):**
```xml
<xsd:attribute name="minlat" type="latitudeType" use="required"/>
<xsd:attribute name="minlon" type="longitudeType" use="required"/>
<xsd:attribute name="maxlat" type="latitudeType" use="required"/>
<xsd:attribute name="maxlon" type="longitudeType" use="required"/>
```

All four attributes are **REQUIRED** in GPX 1.1.

**Current Implementation:**
```php
public function __construct(
    public ?float $minLatitude,
    public ?float $minLongitude,
    public ?float $maxLatitude,
    public ?float $maxLongitude
) {}
```

**Problem:** All properties are nullable, but schema requires them.

**Evidence from Tests:**
- `test_bounds_with_partial_null_values()` - Creates bounds with null values
- This violates GPX 1.1 specification

**Recommendation:**
```php
public function __construct(
    public float $minLatitude,
    public float $minLongitude,
    public float $maxLatitude,
    public float $maxLongitude
) {}
```

---

### 3. Link Model - Missing Required Attribute ❌

**Schema Requirement (linkType):**
```xml
<xsd:attribute name="href" type="xsd:anyURI" use="required"/>
```

The `href` attribute is **REQUIRED**.

**Current Implementation:**
```php
public function __construct(
    public string $href = '',
    public ?string $text = null,
    public ?string $type = null
) {}
```

**Problem:** While `href` is non-nullable, it has a default empty string value. An empty string is not a valid URI.

**Evidence from Tests:**
- `test_link_handles_different_url_schemes()` - Tests various URL schemes, but doesn't validate empty href

**Recommendation:**
```php
public function __construct(
    public string $href,  // Remove default value
    public ?string $text = null,
    public ?string $type = null
) {}
```

---

### 4. Copyright Model - Missing Required Attribute ❌

**Schema Requirement (copyrightType):**
```xml
<xsd:attribute name="author" type="xsd:string" use="required"/>
```

The `author` attribute is **REQUIRED**.

**Current Implementation:**
```php
public function __construct(
    public string $author = '',
    public ?string $year = null,
    public ?string $license = null
) {}
```

**Problem:** While `author` is non-nullable, it has a default empty string. An empty author violates the intent of the required field.

**Recommendation:**
```php
public function __construct(
    public string $author,  // Remove default value
    public ?string $year = null,
    public ?string $license = null
) {}
```

---

### 5. Email Model - Missing Required Attributes ❌

**Schema Requirement (emailType):**
```xml
<xsd:attribute name="id" type="xsd:string" use="required"/>
<xsd:attribute name="domain" type="xsd:string" use="required"/>
```

Both attributes are **REQUIRED**.

**Current Implementation:**
```php
public function __construct(
    public string $id = '',
    public string $domain = ''
) {}
```

**Problem:** Both have default empty strings, allowing invalid email objects.

**Recommendation:**
```php
public function __construct(
    public string $id,
    public string $domain
) {}
```

---

## Validation Issues

### Missing Coordinate Range Validation

**Schema Requirements:**
- `latitudeType`: -90.0 ≤ value ≤ 90.0
- `longitudeType`: -180.0 ≤ value < 180.0
- `degreesType`: 0.0 ≤ value < 360.0 (for magVar)

**Current Implementation:** No validation in models.

**Tests Show:**
- `test_bounds_with_boundary_latitude_values()` - Tests boundary values but doesn't validate
- No validation prevents invalid coordinates like lat=100.0 or lon=200.0

**Recommendation:** Add validation in constructors or setters:
```php
public function __construct(
    PointType|string $pointType,
    float $latitude,
    float $longitude
) {
    if ($latitude < -90.0 || $latitude > 90.0) {
        throw new \InvalidArgumentException("Latitude must be between -90.0 and 90.0");
    }
    if ($longitude < -180.0 || $longitude >= 180.0) {
        throw new \InvalidArgumentException("Longitude must be between -180.0 and 180.0");
    }
    
    $this->latitude = $latitude;
    $this->longitude = $longitude;
    $this->pointType = $pointType instanceof PointType ? $pointType : PointType::from($pointType);
}
```

---

### Missing Fix Type Validation

**Schema Requirement (fixType):**
```xml
<xsd:restriction base="xsd:string">
  <xsd:enumeration value="none"/>
  <xsd:enumeration value="2d"/>
  <xsd:enumeration value="3d"/>
  <xsd:enumeration value="dgps"/>
  <xsd:enumeration value="pps"/>
</xsd:restriction>
```

**Current Implementation:**
```php
public ?string $fix = null;
```

**Problem:** No validation. Any string can be assigned.

**Recommendation:** Create a `FixType` enum:
```php
enum FixType: string {
    case NONE = 'none';
    case TWO_D = '2d';
    case THREE_D = '3d';
    case DGPS = 'dgps';
    case PPS = 'pps';
}

public ?FixType $fix = null;
```

---

### Missing DGPS Station Validation

**Schema Requirement (dgpsStationType):**
```xml
<xsd:restriction base="xsd:integer">
  <xsd:minInclusive value="0"/>
  <xsd:maxInclusive value="1023"/>
</xsd:restriction>
```

**Current Implementation:**
```php
public ?int $dgpsid = null;
```

**Problem:** No validation. Values outside 0-1023 range are allowed.

**Recommendation:** Add validation when setting the value.

---

## Correct Implementations ✅

### GpxFile Model
- Correctly implements optional collections (waypoints, routes, tracks)
- Metadata is optional as per schema
- Extensions handled correctly

### Metadata Model
- All properties correctly optional as per schema
- Proper structure matches metadataType

### Track & Route Models
- Correctly implement rteType and trkType
- Optional properties properly nullable
- Segments/points as collections

### Segment Model
- Correctly implements trksegType
- Points array and extensions match schema

### Person Model
- All properties correctly optional
- Structure matches personType

---

## Summary of Required Changes

### High Priority (Schema Violations)

1. **Point**: Make `latitude` and `longitude` required constructor parameters
2. **Bounds**: Make all four coordinate properties required
3. **Link**: Remove default empty string from `href`
4. **Copyright**: Remove default empty string from `author`
5. **Email**: Remove default empty strings from `id` and `domain`

### Medium Priority (Data Integrity)

6. Add coordinate range validation (-90/90 for lat, -180/180 for lon)
7. Add `magVar` range validation (0-360 degrees)
8. Create `FixType` enum for GPS fix types
9. Add DGPS station ID range validation (0-1023)

### Low Priority (Best Practices)

10. Add validation for `satellitesNumber` (must be positive)
11. Consider validation for dilution of precision values (hdop, vdop, pdop)

---

## Test Impact Analysis

### Tests That Will Break

After implementing required parameters, these test patterns will need updates:

1. **BoundsTest.php**:
   - `test_bounds_with_partial_null_values()` - Invalid test, should be removed
   - All other tests should pass with minor adjustments

2. **Point-related tests**:
   - Any test creating Point without lat/lon will need to provide them
   - Factory methods should be updated

3. **LinkTest.php**:
   - Tests creating Link without href will need updates

### Tests That Validate Compliance

New tests needed:
- Validation tests for coordinate ranges
- Tests for required parameter enforcement
- Enum validation tests for FixType

---

## Migration Strategy

Given the project is in **Phase 4: PHP 8.4 Migration**, this is the perfect time to fix these issues:

### Step 1: Create Enums (PHP 8.1+)
```php
enum FixType: string { /* ... */ }
```

### Step 2: Update Models with Required Parameters
Use constructor property promotion with validation

### Step 3: Update Tests
Fix or remove tests that create invalid GPX structures

### Step 4: Update Documentation
Reflect breaking changes in CHANGELOG.md

### Step 5: Version Bump
This is a breaking change → version 2.0.0

---

## Conclusion

The phpGPX library has several **critical schema compliance issues** where required GPX 1.1 attributes are implemented as optional. This allows creation of invalid GPX files that violate the specification.

**Recommended Action:** Fix these issues during the PHP 8.4 migration phase (current phase) before proceeding to architecture refactoring. This ensures a solid foundation for future improvements.

**Breaking Changes:** Yes - this will require version 2.0.0 as it changes public APIs.

**Test Coverage Impact:** Some existing tests validate invalid behavior and should be removed or updated.
