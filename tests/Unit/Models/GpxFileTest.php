<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use phpGPX\Models\Route;
use phpGPX\Models\Track;
use phpGPX\Models\Metadata;
use phpGPX\Models\Extensions;
use phpGPX\phpGPX;
use phpGPX\Tests\Support\TestCase;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\TrackFactory;
use phpGPX\Tests\Support\Factories\MetadataFactory;

/**
 * Unit tests for GpxFile model.
 */
final class GpxFileTest extends TestCase
{
	public function test_gpx_file_can_be_created(): void
	{
		// Act
		$gpxFile = new GpxFile();

		// Assert
		$this->assertInstanceOf(GpxFile::class, $gpxFile);
		$this->assertIsArray($gpxFile->waypoints);
		$this->assertIsArray($gpxFile->routes);
		$this->assertIsArray($gpxFile->tracks);
		$this->assertEmpty($gpxFile->waypoints);
		$this->assertEmpty($gpxFile->routes);
		$this->assertEmpty($gpxFile->tracks);
		$this->assertNull($gpxFile->metadata);
		$this->assertNull($gpxFile->extensions);
		$this->assertNull($gpxFile->creator);
	}

	public function test_gpx_file_can_be_created_with_tracks(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$track1 = TrackFactory::createWithPoints(5);
		$track2 = TrackFactory::createWithPoints(3);

		// Act
		$gpxFile->tracks[] = $track1;
		$gpxFile->tracks[] = $track2;

		// Assert
		$this->assertCount(2, $gpxFile->tracks);
		$this->assertSame($track1, $gpxFile->tracks[0]);
		$this->assertSame($track2, $gpxFile->tracks[1]);
	}

	public function test_gpx_file_can_be_created_with_routes(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$route = new Route();
		$route->name = 'Test Route';
		// Create route points with ROUTEPOINT type
		$route->points = [
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.0, 'longitude' => 9.0]),
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.01, 'longitude' => 9.01]),
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.02, 'longitude' => 9.02]),
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.03, 'longitude' => 9.03]),
		];

		// Act
		$gpxFile->routes[] = $route;

		// Assert
		$this->assertCount(1, $gpxFile->routes);
		$this->assertSame($route, $gpxFile->routes[0]);
		$this->assertEquals('Test Route', $gpxFile->routes[0]->name);
	}

	public function test_gpx_file_can_be_created_with_waypoints(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$waypoint1 = PointFactory::create(['pointType' => Point::WAYPOINT, 'name' => 'WP1']);
		$waypoint2 = PointFactory::create(['pointType' => Point::WAYPOINT, 'name' => 'WP2']);

		// Act
		$gpxFile->waypoints[] = $waypoint1;
		$gpxFile->waypoints[] = $waypoint2;

		// Assert
		$this->assertCount(2, $gpxFile->waypoints);
		$this->assertEquals('WP1', $gpxFile->waypoints[0]->name);
		$this->assertEquals('WP2', $gpxFile->waypoints[1]->name);
	}

	public function test_gpx_file_with_metadata(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$metadata = MetadataFactory::create([
			'name' => 'My GPX File',
			'description' => 'A test GPX file'
		]);

		// Act
		$gpxFile->metadata = $metadata;

		// Assert
		$this->assertInstanceOf(Metadata::class, $gpxFile->metadata);
		$this->assertEquals('My GPX File', $gpxFile->metadata->name);
		$this->assertEquals('A test GPX file', $gpxFile->metadata->description);
	}

	public function test_gpx_file_with_creator(): void
	{
		// Arrange
		$gpxFile = new GpxFile();

		// Act
		$gpxFile->creator = 'Test Creator v1.0';

		// Assert
		$this->assertEquals('Test Creator v1.0', $gpxFile->creator);
	}

	public function test_gpx_file_to_array_with_empty_file(): void
	{
		// Arrange
		$gpxFile = new GpxFile();

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertIsArray($array);
		$this->assertEmpty($array);
	}

	public function test_gpx_file_to_array_with_tracks(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$track = TrackFactory::createWithPoints(3);
		$track->name = 'Test Track';
		$gpxFile->tracks[] = $track;

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertArrayHasKey('tracks', $array);
		$this->assertIsArray($array['tracks']);
		$this->assertCount(1, $array['tracks']);
		$this->assertEquals('Test Track', $array['tracks'][0]['name']);
	}

	public function test_gpx_file_to_array_with_metadata(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$metadata = MetadataFactory::create(['name' => 'Test File']);
		$gpxFile->metadata = $metadata;

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertArrayHasKey('metadata', $array);
		$this->assertIsArray($array['metadata']);
		$this->assertEquals('Test File', $array['metadata']['name']);
	}

	public function test_gpx_file_to_array_with_creator(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Test Creator';

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertArrayHasKey('creator', $array);
		$this->assertEquals('Test Creator', $array['creator']);
	}

	public function test_gpx_file_to_json(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Test Creator';
		$track = TrackFactory::createWithPoints(2);
		$track->name = 'JSON Track';
		$gpxFile->tracks[] = $track;

		// Act
		$json = $gpxFile->toJSON();

		// Assert
		$this->assertIsString($json);
		$decoded = json_decode($json, true);
		$this->assertIsArray($decoded);
		$this->assertEquals('Test Creator', $decoded['creator']);
		$this->assertEquals('JSON Track', $decoded['tracks'][0]['name']);
	}

	public function test_gpx_file_to_xml_creates_valid_xml(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Test Creator';

		// Act
		$document = $gpxFile->toXML();

		// Assert
		$this->assertInstanceOf(\DOMDocument::class, $document);
		$xml = $document->saveXML();
		$this->assertValidGpxXml($xml);
	}

	public function test_gpx_file_to_xml_includes_creator(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Custom Creator v2.0';

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('creator="Custom Creator v2.0"', $xml);
	}

	public function test_gpx_file_to_xml_uses_default_creator_when_not_set(): void
	{
		// Arrange
		$gpxFile = new GpxFile();

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('creator=', $xml);
		$this->assertStringContainsString('phpGPX', $xml);
	}

	public function test_gpx_file_to_xml_includes_metadata(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$metadata = MetadataFactory::create(['name' => 'XML Test']);
		$gpxFile->metadata = $metadata;

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<metadata>', $xml);
		$this->assertStringContainsString('<name>XML Test</name>', $xml);
	}

	public function test_gpx_file_to_xml_includes_waypoints(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$waypoint = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'name' => 'Test Waypoint',
			'latitude' => 50.0,
			'longitude' => 10.0
		]);
		$gpxFile->waypoints[] = $waypoint;

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<wpt', $xml);
		$this->assertStringContainsString('lat="50"', $xml);
		$this->assertStringContainsString('lon="10"', $xml);
		$this->assertStringContainsString('<name>Test Waypoint</name>', $xml);
	}

	public function test_gpx_file_to_xml_includes_routes(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$route = new Route();
		$route->name = 'Test Route';
		// Create route points with ROUTEPOINT type
		$route->points = [
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.0, 'longitude' => 9.0]),
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.01, 'longitude' => 9.01]),
		];
		$gpxFile->routes[] = $route;

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<rte>', $xml);
		$this->assertStringContainsString('<name>Test Route</name>', $xml);
		$this->assertStringContainsString('<rtept', $xml);
	}

	public function test_gpx_file_to_xml_includes_tracks(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$track = TrackFactory::createWithPoints(2);
		$track->name = 'Test Track';
		$gpxFile->tracks[] = $track;

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<trk>', $xml);
		$this->assertStringContainsString('<name>Test Track</name>', $xml);
		$this->assertStringContainsString('<trkseg>', $xml);
		$this->assertStringContainsString('<trkpt', $xml);
	}

	public function test_gpx_file_to_xml_has_correct_version(): void
	{
		// Arrange
		$gpxFile = new GpxFile();

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('version="1.1"', $xml);
	}

	public function test_gpx_file_to_xml_has_correct_namespace(): void
	{
		// Arrange
		$gpxFile = new GpxFile();

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('http://www.topografix.com/GPX/1/1', $xml);
		$this->assertStringContainsString('xsi:schemaLocation', $xml);
	}

	public function test_gpx_file_with_all_elements(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Complete Test';
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Complete File']);
		$gpxFile->waypoints[] = PointFactory::create(['pointType' => Point::WAYPOINT, 'name' => 'WP1']);
		
		// Create a route with route points
		$route = new Route();
		$route->name = 'Complete Route';
		$route->points = [
			PointFactory::create(['pointType' => Point::ROUTEPOINT, 'latitude' => 54.0, 'longitude' => 9.0]),
		];
		$gpxFile->routes[] = $route;
		
		$gpxFile->tracks[] = TrackFactory::createWithPoints(3);

		// Act
		$array = $gpxFile->toArray();
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert - Array
		$this->assertArrayHasKey('creator', $array);
		$this->assertArrayHasKey('metadata', $array);
		$this->assertArrayHasKey('waypoints', $array);
		$this->assertArrayHasKey('routes', $array);
		$this->assertArrayHasKey('tracks', $array);

		// Assert - XML
		$this->assertValidGpxXml($xml);
		$this->assertStringContainsString('<metadata>', $xml);
		$this->assertStringContainsString('<wpt', $xml);
		$this->assertStringContainsString('<rte>', $xml);
		$this->assertStringContainsString('<trk>', $xml);
	}

	public function test_gpx_file_save_xml_format(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'Save Test';
		$track = TrackFactory::createWithPoints(2);
		$gpxFile->tracks[] = $track;
		$tempFile = sys_get_temp_dir() . '/test_gpx_' . uniqid() . '.gpx';

		// Act
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);

		// Assert
		$this->assertFileExists($tempFile);
		$content = file_get_contents($tempFile);
		$this->assertValidGpxXml($content);
		$this->assertStringContainsString('creator="Save Test"', $content);

		// Cleanup
		unlink($tempFile);
	}

	public function test_gpx_file_save_json_format(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'JSON Save Test';
		$track = TrackFactory::createWithPoints(2);
		$track->name = 'JSON Track';
		$gpxFile->tracks[] = $track;
		$tempFile = sys_get_temp_dir() . '/test_gpx_' . uniqid() . '.json';

		// Act
		$gpxFile->save($tempFile, phpGPX::JSON_FORMAT);

		// Assert
		$this->assertFileExists($tempFile);
		$content = file_get_contents($tempFile);
		$decoded = json_decode($content, true);
		$this->assertIsArray($decoded);
		$this->assertEquals('JSON Save Test', $decoded['creator']);
		$this->assertEquals('JSON Track', $decoded['tracks'][0]['name']);

		// Cleanup
		unlink($tempFile);
	}

	public function test_gpx_file_save_throws_exception_for_unsupported_format(): void
	{
		// Arrange
		$gpxFile = new GpxFile();
		$tempFile = sys_get_temp_dir() . '/test_gpx_' . uniqid() . '.txt';

		// Assert
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Unsupported file format!');

		// Act
		$gpxFile->save($tempFile, 'INVALID_FORMAT');
	}
}

