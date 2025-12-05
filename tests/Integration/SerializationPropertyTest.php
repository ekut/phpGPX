<?php

declare(strict_types=1);

namespace phpGPX\Tests\Integration;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Enums\FileFormat;
use phpGPX\Enums\PointType;
use phpGPX\Models\Bounds;
use phpGPX\Models\Copyright;
use phpGPX\Models\Email;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use phpGPX\Tests\Support\TestCase;

/**
 * Property-based tests for GPX 1.1 schema compliance in serialization.
 *
 * Tests Requirements 1.3, 2.3, 3.3, 4.3, 5.3, 12.2, 12.3
 */
final class SerializationPropertyTest extends TestCase
{
	use TestTrait;

	/**
	 * **Feature: gpx-schema-compliance, Property 2: Point serialization includes coordinates**
	 * **Validates: Requirements 1.3**
	 *
	 * For any Point object, serializing to GPX XML SHALL produce lat and lon as XML attributes.
	 */
	public function test_property_point_serialization_includes_coordinates(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // latitude
				Generators::choose(-180, 179),   // longitude (exclusive of 180)
			)
			->withMaxSize(100)
			->then(function ($latBase, $lonBase): void {
				// Convert to floats with decimal precision
				$latitude = (float) $latBase + (mt_rand(0, 999999) / 1000000);
				$longitude = (float) $lonBase + (mt_rand(0, 999999) / 1000000);

				// Ensure within valid ranges
				$latitude = min(90.0, max(-90.0, $latitude));
				$longitude = min(179.999999, max(-180.0, $longitude));

				// Create a GPX file with a point
				$gpxFile = new GpxFile('Property Test Creator');
				$track = new Track();
				$segment = new Segment();

				$point = new Point(PointType::TRACKPOINT, $latitude, $longitude);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Register namespace
				$xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

				// Find the track point
				$trkpts = $xml->xpath('//gpx:trkpt');

				$this->assertNotEmpty($trkpts, 'Should have at least one track point');

				$trkpt = $trkpts[0];

				// Verify lat and lon are attributes (not elements)
				$this->assertNotNull($trkpt['lat'], 'lat should be an XML attribute');
				$this->assertNotNull($trkpt['lon'], 'lon should be an XML attribute');

				// Verify values are present and numeric
				$serializedLat = (float) $trkpt['lat'];
				$serializedLon = (float) $trkpt['lon'];

				$this->assertIsFloat($serializedLat, 'Latitude should be a float');
				$this->assertIsFloat($serializedLon, 'Longitude should be a float');

				// Verify values match (with small tolerance for serialization)
				$epsilon = 0.000001;
				$this->assertEqualsWithDelta(
					$latitude,
					$serializedLat,
					$epsilon,
					'Serialized latitude should match original',
				);
				$this->assertEqualsWithDelta(
					$longitude,
					$serializedLon,
					$epsilon,
					'Serialized longitude should match original',
				);

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 6: Bounds serialization includes all coordinates**
	 * **Validates: Requirements 2.3**
	 *
	 * For any Bounds object, serializing to GPX XML SHALL produce minlat, minlon, maxlat, and maxlon as XML attributes.
	 */
	public function test_property_bounds_serialization_includes_all_coordinates(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(-90, 90),    // minLat
				Generators::choose(-180, 179),  // minLon
				Generators::choose(-90, 90),    // maxLat
				Generators::choose(-180, 179),   // maxLon
			)
			->withMaxSize(100)
			->then(function ($minLatBase, $minLonBase, $maxLatBase, $maxLonBase): void {
				// Convert to floats
				$minLat = (float) $minLatBase;
				$minLon = (float) $minLonBase;
				$maxLat = (float) $maxLatBase;
				$maxLon = (float) $maxLonBase;

				// Ensure min <= max
				if ($minLat > $maxLat) {
					[$minLat, $maxLat] = [$maxLat, $minLat];
				}
				if ($minLon > $maxLon) {
					[$minLon, $maxLon] = [$maxLon, $minLon];
				}

				// Create a GPX file with bounds
				$gpxFile = new GpxFile('Property Test Creator');
				$metadata = new Metadata();
				$metadata->bounds = new Bounds($minLat, $minLon, $maxLat, $maxLon);
				$gpxFile->metadata = $metadata;

				// Add a minimal track (required for valid GPX)
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Register namespace
				$xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

				// Find the bounds element
				$bounds = $xml->xpath('//gpx:bounds');

				$this->assertNotEmpty($bounds, 'Should have bounds element');

				$boundsElement = $bounds[0];

				// Verify all four coordinates are attributes
				$this->assertNotNull($boundsElement['minlat'], 'minlat should be an XML attribute');
				$this->assertNotNull($boundsElement['minlon'], 'minlon should be an XML attribute');
				$this->assertNotNull($boundsElement['maxlat'], 'maxlat should be an XML attribute');
				$this->assertNotNull($boundsElement['maxlon'], 'maxlon should be an XML attribute');

				// Verify values match
				$epsilon = 0.000001;
				$this->assertEqualsWithDelta(
					$minLat,
					(float) $boundsElement['minlat'],
					$epsilon,
					'Serialized minlat should match original',
				);
				$this->assertEqualsWithDelta(
					$minLon,
					(float) $boundsElement['minlon'],
					$epsilon,
					'Serialized minlon should match original',
				);
				$this->assertEqualsWithDelta(
					$maxLat,
					(float) $boundsElement['maxlat'],
					$epsilon,
					'Serialized maxlat should match original',
				);
				$this->assertEqualsWithDelta(
					$maxLon,
					(float) $boundsElement['maxlon'],
					$epsilon,
					'Serialized maxlon should match original',
				);

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 11: Link serialization includes href**
	 * **Validates: Requirements 3.3**
	 *
	 * For any Link object, serializing to GPX XML SHALL produce a non-empty href attribute.
	 */
	public function test_property_link_serialization_includes_href(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 100),  // Generate a number to create unique hrefs
			)
			->withMaxSize(100)
			->then(function ($num): void {
				// Create a non-empty href
				$href = "https://example.com/link{$num}";

				// Create a GPX file with a link
				$gpxFile = new GpxFile('Property Test Creator');
				$metadata = new Metadata();
				$metadata->links[] = new Link($href);
				$gpxFile->metadata = $metadata;

				// Add a minimal track
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Register namespace
				$xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

				// Find the link element
				$links = $xml->xpath('//gpx:metadata/gpx:link');

				$this->assertNotEmpty($links, 'Should have link element');

				$linkElement = $links[0];

				// Verify href is an attribute and non-empty
				$this->assertNotNull($linkElement['href'], 'href should be an XML attribute');
				$serializedHref = (string) $linkElement['href'];
				$this->assertNotEmpty($serializedHref, 'href should not be empty');
				$this->assertEquals($href, $serializedHref, 'Serialized href should match original');

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 14: Copyright serialization includes author**
	 * **Validates: Requirements 4.3**
	 *
	 * For any Copyright object, serializing to GPX XML SHALL produce a non-empty author attribute.
	 */
	public function test_property_copyright_serialization_includes_author(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 100),  // Generate a number to create unique authors
			)
			->withMaxSize(100)
			->then(function ($num): void {
				// Create a non-empty author
				$author = "Test Author {$num}";

				// Create a GPX file with copyright
				$gpxFile = new GpxFile('Property Test Creator');
				$metadata = new Metadata();
				$metadata->copyright = new Copyright($author);
				$gpxFile->metadata = $metadata;

				// Add a minimal track
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Register namespace
				$xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

				// Find the copyright element
				$copyrights = $xml->xpath('//gpx:copyright');

				$this->assertNotEmpty($copyrights, 'Should have copyright element');

				$copyrightElement = $copyrights[0];

				// Verify author is an attribute and non-empty
				$this->assertNotNull($copyrightElement['author'], 'author should be an XML attribute');
				$serializedAuthor = (string) $copyrightElement['author'];
				$this->assertNotEmpty($serializedAuthor, 'author should not be empty');
				$this->assertEquals($author, $serializedAuthor, 'Serialized author should match original');

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 17: Email serialization includes both attributes**
	 * **Validates: Requirements 5.3**
	 *
	 * For any Email object, serializing to GPX XML SHALL produce non-empty id and domain attributes.
	 */
	public function test_property_email_serialization_includes_both_attributes(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 100),  // Generate a number for unique id
				Generators::choose(1, 100),   // Generate a number for unique domain
			)
			->withMaxSize(100)
			->then(function ($idNum, $domainNum): void {
				// Create non-empty values
				$id = "user{$idNum}";
				$domain = "example{$domainNum}.com";

				// Create a GPX file with email
				$gpxFile = new GpxFile('Property Test Creator');
				$metadata = new Metadata();
				$author = new Person();
				$author->email = new Email($id, $domain);
				$metadata->author = $author;
				$gpxFile->metadata = $metadata;

				// Add a minimal track
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Register namespace
				$xml->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

				// Find the email element
				$emails = $xml->xpath('//gpx:email');

				$this->assertNotEmpty($emails, 'Should have email element');

				$emailElement = $emails[0];

				// Verify both id and domain are attributes and non-empty
				$this->assertNotNull($emailElement['id'], 'id should be an XML attribute');
				$this->assertNotNull($emailElement['domain'], 'domain should be an XML attribute');

				$serializedId = (string) $emailElement['id'];
				$serializedDomain = (string) $emailElement['domain'];

				$this->assertNotEmpty($serializedId, 'id should not be empty');
				$this->assertNotEmpty($serializedDomain, 'domain should not be empty');
				$this->assertEquals($id, $serializedId, 'Serialized id should match original');
				$this->assertEquals($domain, $serializedDomain, 'Serialized domain should match original');

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 30: GpxFile version serialization**
	 * **Validates: Requirements 12.2**
	 *
	 * For any GpxFile object, serializing to GPX XML SHALL produce a version attribute with the fixed value "1.1".
	 */
	public function test_property_gpxfile_version_serialization(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 100),  // Generate a number to create unique creators
			)
			->withMaxSize(100)
			->then(function ($num): void {
				// Create a non-empty creator
				$creator = "Test Creator {$num}";

				// Create a GPX file
				$gpxFile = new GpxFile($creator);

				// Add a minimal track
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Verify version attribute is "1.1"
				$this->assertNotNull($xml['version'], 'version should be an XML attribute');
				$version = (string) $xml['version'];
				$this->assertEquals('1.1', $version, 'Version should always be "1.1"');

				// Cleanup
				unlink($tempFile);
			});
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 31: GpxFile creator serialization**
	 * **Validates: Requirements 12.3**
	 *
	 * For any GpxFile object, serializing to GPX XML SHALL produce a non-empty creator attribute.
	 */
	public function test_property_gpxfile_creator_serialization(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(1, 100),  // Generate a number to create unique creators
			)
			->withMaxSize(100)
			->then(function ($num): void {
				// Create a non-empty creator
				$creator = "Test Creator {$num}";

				// Create a GPX file
				$gpxFile = new GpxFile($creator);

				// Add a minimal track
				$track = new Track();
				$segment = new Segment();
				$point = new Point(PointType::TRACKPOINT, 54.0, 9.0);
				$segment->points[] = $point;
				$track->segments[] = $segment;
				$gpxFile->tracks[] = $track;

				// Serialize to XML
				$tempFile = tempnam(sys_get_temp_dir(), 'gpx_prop_test_');
				$gpxFile->save($tempFile, FileFormat::XML);

				// Read and parse XML
				$xmlContent = file_get_contents($tempFile);
				$xml = simplexml_load_string($xmlContent);

				// Verify creator attribute is present and non-empty
				$this->assertNotNull($xml['creator'], 'creator should be an XML attribute');
				$serializedCreator = (string) $xml['creator'];
				$this->assertNotEmpty($serializedCreator, 'creator should not be empty');
				$this->assertEquals($creator, $serializedCreator, 'Serialized creator should match original');

				// Cleanup
				unlink($tempFile);
			});
	}
}
