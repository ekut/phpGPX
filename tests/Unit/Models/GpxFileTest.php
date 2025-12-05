<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use DOMDocument;
use Eris\Generators;
use Eris\TestTrait;
use InvalidArgumentException;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Metadata;
use phpGPX\Models\Point;
use phpGPX\Models\Route;
use phpGPX\phpGPX;
use phpGPX\Tests\Support\Factories\MetadataFactory;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\TrackFactory;
use phpGPX\Tests\Support\TestCase;
use RuntimeException;
use TypeError;

/**
 * Unit tests for GpxFile model.
 */
final class GpxFileTest extends TestCase
{
	use TestTrait;

	public function test_gpx_file_can_be_created(): void
	{
		// Act
		$gpxFile = new GpxFile('Test Creator');

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
		$this->assertEquals('Test Creator', $gpxFile->creator);
	}

	public function test_gpx_file_requires_creator(): void
	{
		// Assert
		$this->expectException(TypeError::class);

		// Act
		new GpxFile();
	}

	public function test_gpx_file_rejects_empty_creator(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('GPX creator');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new GpxFile('');
	}

	public function test_gpx_file_rejects_whitespace_only_creator(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('GPX creator');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new GpxFile('   ');
	}

	public function test_gpx_file_accepts_valid_creator(): void
	{
		// Act
		$gpxFile = new GpxFile('My Application v1.0');

		// Assert
		$this->assertEquals('My Application v1.0', $gpxFile->creator);
	}

	public function test_gpx_file_can_be_created_with_tracks(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
		$metadata = MetadataFactory::create([
			'name' => 'My GPX File',
			'description' => 'A test GPX file',
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
		$gpxFile = new GpxFile('Initial Creator');

		// Act
		$gpxFile->creator = 'Test Creator v1.0';

		// Assert
		$this->assertEquals('Test Creator v1.0', $gpxFile->creator);
	}

	public function test_gpx_file_to_array_with_empty_file(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('creator', $array);
		$this->assertEquals('Test Creator', $array['creator']);
	}

	public function test_gpx_file_to_array_with_tracks(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');

		// Act
		$array = $gpxFile->toArray();

		// Assert
		$this->assertArrayHasKey('creator', $array);
		$this->assertEquals('Test Creator', $array['creator']);
	}

	public function test_gpx_file_to_json(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');

		// Act
		$document = $gpxFile->toXML();

		// Assert
		$this->assertInstanceOf(DOMDocument::class, $document);
		$xml = $document->saveXML();
		$this->assertValidGpxXml($xml);
	}

	public function test_gpx_file_to_xml_includes_creator(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Custom Creator v2.0');

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('creator="Custom Creator v2.0"', $xml);
	}

	public function test_gpx_file_to_xml_always_includes_creator(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Required Creator');

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('creator="Required Creator"', $xml);
	}

	public function test_gpx_file_to_xml_includes_metadata(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
		$waypoint = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'name' => 'Test Waypoint',
			'latitude' => 50.0,
			'longitude' => 10.0,
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
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');
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
		$gpxFile = new GpxFile('Test Creator');

		// Act
		$document = $gpxFile->toXML();
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('version="1.1"', $xml);
	}

	public function test_gpx_file_to_xml_has_correct_namespace(): void
	{
		// Arrange
		$gpxFile = new GpxFile('Test Creator');

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
		$gpxFile = new GpxFile('Complete Test');
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
		$gpxFile = new GpxFile('Save Test');
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
		$gpxFile = new GpxFile('JSON Save Test');
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
		$gpxFile = new GpxFile('Test Creator');
		$tempFile = sys_get_temp_dir() . '/test_gpx_' . uniqid() . '.txt';

		// Assert
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Unsupported file format!');

		// Act
		$gpxFile->save($tempFile, 'INVALID_FORMAT');
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 28: GpxFile requires creator**
	 * **Validates: Requirements 12.1**
	 */
	public function test_property_gpx_file_requires_creator(): void
	{
		// This property is tested by the type system
		// Attempting to create GpxFile without creator throws TypeError
		$this->expectException(TypeError::class);
		new GpxFile();
	}

	/**
	 * **Feature: gpx-schema-compliance, Property 29: GpxFile creator validation**
	 * **Validates: Requirements 12.4**
	 */
	public function test_property_gpx_file_creator_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements(['', ' ', '  ', "\t", "\n", "   \t\n   "]),
			)
			->then(function ($emptyCreator): void {
				try {
					new GpxFile($emptyCreator);
					$this->fail('Expected InvalidArgumentException for empty/whitespace creator: ' . json_encode($emptyCreator));
				} catch (InvalidArgumentException $e) {
					// Verify error message contains required information
					$this->assertStringContainsString('GPX creator', $e->getMessage());
					$this->assertStringContainsString('required', $e->getMessage());
					$this->assertStringContainsString('cannot be empty', $e->getMessage());
				}
			});
	}
}
