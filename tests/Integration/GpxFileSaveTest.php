<?php

declare(strict_types=1);

namespace phpGPX\Tests\Integration;

use DateTime;
use phpGPX\Enums\FileFormat;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\phpGPX;
use phpGPX\Tests\Support\Factories\MetadataFactory;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\TrackFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Integration tests for saving GPX files.
 *
 * Tests Requirements 4.3
 */
final class GpxFileSaveTest extends TestCase
{
	/**
	 * Test creating GpxFile from scratch and saving to XML.
	 *
	 * Requirements: 4.3
	 */
	public function test_create_gpx_file_from_scratch_and_save_to_xml(): void
	{
		// Arrange - Create a GPX file from scratch
		$gpxFile = new GpxFile('phpGPX Test Suite');

		// Add metadata
		$gpxFile->metadata = MetadataFactory::create([
			'name' => 'Test GPX from Scratch',
			'description' => 'A GPX file created programmatically for testing',
			'time' => new DateTime('2024-01-15T10:30:00Z'),
		]);

		// Add a track with points
		$track = TrackFactory::createWithPoints(3);
		$track->name = 'Test Track';
		$track->description = 'A test track with 3 points';
		$gpxFile->tracks[] = $track;

		// Add waypoints
		$waypoint1 = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'latitude' => 54.9328621088893,
			'longitude' => 9.860624216140083,
			'name' => 'Start Point',
			'description' => 'The starting waypoint',
		]);
		$gpxFile->waypoints[] = $waypoint1;

		$waypoint2 = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'latitude' => 54.9428621088893,
			'longitude' => 9.870624216140083,
			'name' => 'End Point',
			'description' => 'The ending waypoint',
		]);
		$gpxFile->waypoints[] = $waypoint2;

		// Act - Save to temporary file
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		// Assert - File exists and contains valid XML
		$this->assertFileExists($tempFile, 'Saved GPX file should exist');

		$xmlContent = file_get_contents($tempFile);
		$this->assertNotEmpty($xmlContent, 'Saved file should not be empty');
		$this->assertValidGpxXml($xmlContent);

		// Assert - XML contains expected elements
		$this->assertStringContainsString('Test GPX from Scratch', $xmlContent);
		$this->assertStringContainsString('Test Track', $xmlContent);
		$this->assertStringContainsString('Start Point', $xmlContent);
		$this->assertStringContainsString('End Point', $xmlContent);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saved XML is valid GPX 1.1 format.
	 *
	 * Requirements: 4.3
	 */
	public function test_saved_xml_is_valid_gpx_1_1_format(): void
	{
		// Arrange - Create a simple GPX file
		$gpxFile = new GpxFile('phpGPX Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Format Test']);

		$track = TrackFactory::createWithPoints(2);
		$gpxFile->tracks[] = $track;

		// Act - Save to temporary file
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_format_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		// Assert - Load and validate XML structure
		$xmlContent = file_get_contents($tempFile);
		$xml = simplexml_load_string($xmlContent);

		// Check GPX version
		$this->assertEquals('1.1', (string)$xml['version'], 'GPX version should be 1.1');

		// Check creator attribute
		$this->assertNotEmpty((string)$xml['creator'], 'Creator attribute should be present');

		// Check namespace
		$namespaces = $xml->getNamespaces(true);
		$this->assertArrayHasKey('', $namespaces, 'Default namespace should be present');
		$this->assertEquals('http://www.topografix.com/GPX/1/1', $namespaces[''], 'Should use GPX 1.1 namespace');

		// Check schema location
		$xsiNamespace = 'http://www.w3.org/2001/XMLSchema-instance';
		$schemaLocation = $xml->attributes($xsiNamespace)->schemaLocation;
		$schemaLocationStr = (string)$schemaLocation;
		$this->assertNotEmpty($schemaLocationStr, 'Schema location should be present');
		$this->assertStringContainsString('http://www.topografix.com/GPX/1/1', $schemaLocationStr);
		$this->assertStringContainsString('gpx.xsd', $schemaLocationStr);

		// Check required elements structure
		$this->assertNotEmpty($xml->metadata, 'Metadata element should be present');
		$this->assertNotEmpty($xml->trk, 'Track element should be present');

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saved XML can be parsed by external validators (round-trip).
	 *
	 * Requirements: 4.3
	 */
	public function test_saved_xml_can_be_parsed_by_external_validators(): void
	{
		// Arrange - Create a comprehensive GPX file
		$gpxFile = new GpxFile('phpGPX Test Suite');

		// Add metadata with various fields
		$metadata = MetadataFactory::create([
			'name' => 'Comprehensive Test',
			'description' => 'Testing all GPX elements',
			'time' => new DateTime('2024-01-15T12:00:00Z'),
		]);
		$gpxFile->metadata = $metadata;

		// Add track with multiple segments
		$track = TrackFactory::createWithSegments(2, 3);
		$track->name = 'Multi-Segment Track';
		$gpxFile->tracks[] = $track;

		// Add waypoints
		$waypoint = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'latitude' => 54.9328621088893,
			'longitude' => 9.860624216140083,
			'elevation' => 42.5,
			'name' => 'Test Waypoint',
		]);
		$gpxFile->waypoints[] = $waypoint;

		// Act - Save to temporary file
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_parse_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		// Assert - Parse the saved file using phpGPX (simulating external parser)
		$gpx = new phpGPX();
		$parsedFile = $gpx->load($tempFile);

		$this->assertInstanceOf(GpxFile::class, $parsedFile, 'Should parse back to GpxFile');

		// Verify metadata
		$this->assertNotNull($parsedFile->metadata, 'Parsed file should have metadata');
		$this->assertEquals('Comprehensive Test', $parsedFile->metadata->name);
		$this->assertEquals('Testing all GPX elements', $parsedFile->metadata->description);

		// Verify tracks
		$this->assertCount(1, $parsedFile->tracks, 'Should have 1 track');
		$this->assertEquals('Multi-Segment Track', $parsedFile->tracks[0]->name);
		$this->assertCount(2, $parsedFile->tracks[0]->segments, 'Track should have 2 segments');
		$this->assertCount(3, $parsedFile->tracks[0]->segments[0]->points, 'First segment should have 3 points');
		$this->assertCount(3, $parsedFile->tracks[0]->segments[1]->points, 'Second segment should have 3 points');

		// Verify waypoints
		$this->assertCount(1, $parsedFile->waypoints, 'Should have 1 waypoint');
		$this->assertEquals('Test Waypoint', $parsedFile->waypoints[0]->name);
		$this->assertCoordinatesEqual(54.9328621088893, $parsedFile->waypoints[0]->latitude);
		$this->assertCoordinatesEqual(9.860624216140083, $parsedFile->waypoints[0]->longitude);
		$this->assertCoordinatesEqual(42.5, $parsedFile->waypoints[0]->elevation);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving GPX file with routes.
	 *
	 * Requirements: 4.3
	 */
	public function test_save_gpx_file_with_routes(): void
	{
		// Arrange - Create GPX file with routes
		$gpxFile = new GpxFile('phpGPX Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Route Test']);

		// Create a route with points
		$route = new \phpGPX\Models\Route();
		$route->name = 'Test Route';
		$route->description = 'A test route';

		// Add route points
		$routePoint1 = PointFactory::create([
			'pointType' => Point::ROUTEPOINT,
			'latitude' => 54.9328621088893,
			'longitude' => 9.860624216140083,
			'name' => 'Route Point 1',
		]);
		$route->points[] = $routePoint1;

		$routePoint2 = PointFactory::create([
			'pointType' => Point::ROUTEPOINT,
			'latitude' => 54.9428621088893,
			'longitude' => 9.870624216140083,
			'name' => 'Route Point 2',
		]);
		$route->points[] = $routePoint2;

		$gpxFile->routes[] = $route;

		// Act - Save and reload
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_route_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		$gpx = new phpGPX();
		$parsedFile = $gpx->load($tempFile);

		// Assert
		$this->assertCount(1, $parsedFile->routes, 'Should have 1 route');
		$this->assertEquals('Test Route', $parsedFile->routes[0]->name);
		$this->assertEquals('A test route', $parsedFile->routes[0]->description);
		$this->assertCount(2, $parsedFile->routes[0]->points, 'Route should have 2 points');
		$this->assertEquals('Route Point 1', $parsedFile->routes[0]->points[0]->name);
		$this->assertEquals('Route Point 2', $parsedFile->routes[0]->points[1]->name);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving GPX file with all element types (tracks, routes, waypoints).
	 *
	 * Requirements: 4.3
	 */
	public function test_save_gpx_file_with_all_element_types(): void
	{
		// Arrange - Create comprehensive GPX file
		$gpxFile = new GpxFile('phpGPX Complete Test');
		$gpxFile->metadata = MetadataFactory::create([
			'name' => 'Complete GPX',
			'description' => 'Contains tracks, routes, and waypoints',
		]);

		// Add track
		$track = TrackFactory::createWithPoints(2);
		$track->name = 'Test Track';
		$gpxFile->tracks[] = $track;

		// Add route
		$route = new \phpGPX\Models\Route();
		$route->name = 'Test Route';
		$route->points[] = PointFactory::create(['pointType' => Point::ROUTEPOINT]);
		$gpxFile->routes[] = $route;

		// Add waypoint
		$waypoint = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'name' => 'Test Waypoint',
		]);
		$gpxFile->waypoints[] = $waypoint;

		// Act - Save and reload
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_complete_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		$gpx = new phpGPX();
		$parsedFile = $gpx->load($tempFile);

		// Assert - All element types are present
		$this->assertCount(1, $parsedFile->tracks, 'Should have 1 track');
		$this->assertCount(1, $parsedFile->routes, 'Should have 1 route');
		$this->assertCount(1, $parsedFile->waypoints, 'Should have 1 waypoint');

		$this->assertEquals('Test Track', $parsedFile->tracks[0]->name);
		$this->assertEquals('Test Route', $parsedFile->routes[0]->name);
		$this->assertEquals('Test Waypoint', $parsedFile->waypoints[0]->name);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving minimal GPX file (only required elements).
	 *
	 * Requirements: 4.3
	 */
	public function test_save_minimal_gpx_file(): void
	{
		// Arrange - Create minimal GPX file with required creator
		$gpxFile = new GpxFile('Minimal Test Creator');

		// Add minimal track
		$track = TrackFactory::createWithPoints(1);
		$gpxFile->tracks[] = $track;

		// Act - Save
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_minimal_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		// Assert - File is valid
		$xmlContent = file_get_contents($tempFile);
		$this->assertValidGpxXml($xmlContent);

		// Assert - Has creator
		$xml = simplexml_load_string($xmlContent);
		$creator = (string)$xml['creator'];
		$this->assertEquals('Minimal Test Creator', $creator);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving GPX file to JSON format.
	 *
	 * Requirements: 4.3
	 */
	public function test_save_gpx_file_to_json_format(): void
	{
		// Arrange
		$gpxFile = new GpxFile('phpGPX JSON Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'JSON Test']);

		$track = TrackFactory::createWithPoints(2);
		$track->name = 'JSON Track';
		$gpxFile->tracks[] = $track;

		// Act - Save to JSON
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_json_test_');
		$gpxFile->save($tempFile, FileFormat::JSON);

		// Assert - File exists and contains valid JSON
		$this->assertFileExists($tempFile);

		$jsonContent = file_get_contents($tempFile);
		$this->assertNotEmpty($jsonContent);

		$data = json_decode($jsonContent, true);
		$this->assertIsArray($data, 'Should be valid JSON');
		$this->assertArrayHasKey('creator', $data);
		$this->assertArrayHasKey('metadata', $data);
		$this->assertArrayHasKey('tracks', $data);

		$this->assertEquals('phpGPX JSON Test', $data['creator']);
		$this->assertEquals('JSON Test', $data['metadata']['name']);
		$this->assertCount(1, $data['tracks']);
		$this->assertEquals('JSON Track', $data['tracks'][0]['name']);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving GPX file with pretty print enabled.
	 *
	 * Requirements: 4.3
	 */
	public function test_save_gpx_file_with_pretty_print(): void
	{
		// Arrange
		phpGPX::$PRETTY_PRINT = true;

		$gpxFile = new GpxFile('phpGPX Pretty Print Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Pretty Print Test']);
		$track = TrackFactory::createWithPoints(2);
		$gpxFile->tracks[] = $track;

		// Act
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_pretty_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		// Assert - XML should be formatted with newlines and indentation
		$xmlContent = file_get_contents($tempFile);
		$this->assertStringContainsString("\n", $xmlContent, 'Should contain newlines');
		$this->assertStringContainsString("  ", $xmlContent, 'Should contain indentation');

		// Verify it's still valid
		$this->assertValidGpxXml($xmlContent);

		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test saving GPX file with coordinates at various precision levels.
	 *
	 * Requirements: 4.3
	 */
	public function test_save_gpx_file_preserves_coordinate_precision(): void
	{
		// Arrange - Create points with high precision coordinates
		$gpxFile = new GpxFile('phpGPX Precision Test');
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Precision Test']);

		$track = TrackFactory::create();
		$segment = new \phpGPX\Models\Segment();

		// Add point with high precision
		$point = PointFactory::create([
			'latitude' => 54.93286210888934567,
			'longitude' => 9.86062421614008345,
			'elevation' => 42.567890,
		]);
		$segment->points[] = $point;

		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;

		// Act - Save and reload
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_precision_test_');
		$gpxFile->save($tempFile, FileFormat::XML);

		$gpx = new phpGPX();
		$parsedFile = $gpx->load($tempFile);

		// Assert - Coordinates should be preserved with reasonable precision
		$parsedPoint = $parsedFile->tracks[0]->segments[0]->points[0];
		$this->assertCoordinatesEqual(54.93286210888934567, $parsedPoint->latitude, 0.000001);
		$this->assertCoordinatesEqual(9.86062421614008345, $parsedPoint->longitude, 0.000001);
		$this->assertCoordinatesEqual(42.567890, $parsedPoint->elevation, 0.001);

		// Cleanup
		unlink($tempFile);
	}
}
