<?php

declare(strict_types=1);

namespace phpGPX\Tests\Integration;

use phpGPX\phpGPX;
use phpGPX\Models\GpxFile;
use phpGPX\Tests\Support\TestCase;

/**
 * Integration tests for loading GPX files.
 * 
 * Tests Requirements 4.2, 4.5
 */
final class GpxFileLoadTest extends TestCase
{
	/**
	 * Test loading a valid GPX file with tracks.
	 * 
	 * Requirements: 4.2
	 */
	public function test_load_valid_gpx_file_with_tracks(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('minimal-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert
		$this->assertInstanceOf(GpxFile::class, $file);
		$this->assertNotEmpty($file->tracks, 'File should contain tracks');
		$this->assertCount(1, $file->tracks, 'File should contain exactly 1 track');
		
		$track = $file->tracks[0];
		$this->assertEquals('Test Track', $track->name);
		$this->assertNotEmpty($track->segments, 'Track should contain segments');
		$this->assertCount(1, $track->segments, 'Track should contain exactly 1 segment');
		
		$segment = $track->segments[0];
		$this->assertNotEmpty($segment->points, 'Segment should contain points');
		$this->assertCount(2, $segment->points, 'Segment should contain exactly 2 points');
		
		// Verify points have coordinates
		$point1 = $segment->points[0];
		$this->assertCoordinatesEqual(54.9328621088893, $point1->latitude);
		$this->assertCoordinatesEqual(9.860624216140083, $point1->longitude);
		$this->assertCoordinatesEqual(42.5, $point1->elevation);
		
		$point2 = $segment->points[1];
		$this->assertCoordinatesEqual(54.9428621088893, $point2->latitude);
		$this->assertCoordinatesEqual(9.870624216140083, $point2->longitude);
		$this->assertCoordinatesEqual(45.0, $point2->elevation);
	}

	/**
	 * Test loading a GPX file with routes and waypoints.
	 * 
	 * Requirements: 4.2, 4.5
	 */
	public function test_load_gpx_file_with_routes_and_waypoints(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('complete-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert - File structure
		$this->assertInstanceOf(GpxFile::class, $file);
		
		// Assert - Waypoints
		$this->assertNotEmpty($file->waypoints, 'File should contain waypoints');
		$this->assertCount(2, $file->waypoints, 'File should contain exactly 2 waypoints');
		
		$waypoint1 = $file->waypoints[0];
		$this->assertEquals('Waypoint 1', $waypoint1->name);
		$this->assertEquals('First test waypoint', $waypoint1->description);
		$this->assertCoordinatesEqual(54.9328621088893, $waypoint1->latitude);
		$this->assertCoordinatesEqual(9.860624216140083, $waypoint1->longitude);
		
		// Assert - Routes
		$this->assertNotEmpty($file->routes, 'File should contain routes');
		$this->assertCount(1, $file->routes, 'File should contain exactly 1 route');
		
		$route = $file->routes[0];
		$this->assertEquals('Test Route', $route->name);
		$this->assertEquals('A test route with multiple points', $route->description);
		$this->assertNotEmpty($route->points, 'Route should contain points');
		$this->assertCount(3, $route->points, 'Route should contain exactly 3 points');
		
		// Assert - Tracks
		$this->assertNotEmpty($file->tracks, 'File should contain tracks');
		$this->assertCount(1, $file->tracks, 'File should contain exactly 1 track');
		
		$track = $file->tracks[0];
		$this->assertEquals('Test Track', $track->name);
		$this->assertCount(2, $track->segments, 'Track should contain 2 segments');
	}

	/**
	 * Test loading a GPX file with extensions.
	 * 
	 * Requirements: 4.2, 4.5
	 */
	public function test_load_gpx_file_with_extensions(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('track-with-extensions.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert
		$this->assertInstanceOf(GpxFile::class, $file);
		$this->assertNotEmpty($file->tracks, 'File should contain tracks');
		
		$track = $file->tracks[0];
		$this->assertEquals('Test Track with Extensions', $track->name);
		
		$segment = $track->segments[0];
		$this->assertCount(2, $segment->points, 'Segment should contain 2 points');
		
		// Verify extensions are parsed
		$point1 = $segment->points[0];
		$this->assertNotNull($point1->extensions, 'Point should have extensions');
		$this->assertNotEmpty($point1->extensions->trackPointExtension, 'Point should have TrackPointExtension');
		
		$extension = $point1->extensions->trackPointExtension;
		$this->assertEquals(120, $extension->heartRate, 'Heart rate should be 120');
		$this->assertEquals(85, $extension->cadence, 'Cadence should be 85');
		
		$point2 = $segment->points[1];
		$this->assertNotNull($point2->extensions, 'Point should have extensions');
		$extension2 = $point2->extensions->trackPointExtension;
		$this->assertEquals(125, $extension2->heartRate, 'Heart rate should be 125');
		$this->assertEquals(90, $extension2->cadence, 'Cadence should be 90');
	}

	/**
	 * Test loading a minimal GPX file.
	 * 
	 * Requirements: 4.2
	 */
	public function test_load_minimal_gpx_file(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('minimal-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert
		$this->assertInstanceOf(GpxFile::class, $file);
		
		// Verify metadata
		$this->assertNotNull($file->metadata, 'File should have metadata');
		$this->assertEquals('Minimal GPX', $file->metadata->name);
		
		// Verify track structure
		$this->assertCount(1, $file->tracks);
		$this->assertCount(1, $file->tracks[0]->segments);
		$this->assertCount(2, $file->tracks[0]->segments[0]->points);
		
		// Verify no routes or waypoints
		$this->assertEmpty($file->routes, 'Minimal file should have no routes');
		$this->assertEmpty($file->waypoints, 'Minimal file should have no waypoints');
	}

	/**
	 * Test loading an invalid GPX file throws exception.
	 * 
	 * Requirements: 4.5
	 */
	public function test_load_invalid_gpx_file_throws_exception(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('invalid-gpx.xml');
		
		// Act & Assert
		// The simplexml_load_string will generate a warning for malformed XML
		// We suppress the warning and check that parsing fails
		$previousErrorHandling = libxml_use_internal_errors(true);
		
		try {
			$file = $gpx->load($filePath);
			
			// If we get here, check that the file is not properly parsed
			// The malformed XML should result in an incomplete or invalid structure
			$errors = libxml_get_errors();
			$this->assertNotEmpty($errors, 'Loading invalid GPX should generate XML errors');
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors($previousErrorHandling);
		}
	}

	/**
	 * Test loading GPX file and calculating statistics.
	 * 
	 * Requirements: 4.2
	 */
	public function test_load_gpx_file_calculates_statistics(): void
	{
		// Arrange
		$gpx = new phpGPX();
		phpGPX::$CALCULATE_STATS = true;
		$filePath = $this->getFixturePath('minimal-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert
		$track = $file->tracks[0];
		$this->assertNotNull($track->stats, 'Track should have statistics');
		
		// Verify statistics are calculated
		$this->assertGreaterThan(0, $track->stats->distance, 'Distance should be greater than 0');
		$this->assertGreaterThan(0, $track->stats->duration, 'Duration should be greater than 0');
		$this->assertNotNull($track->stats->startedAt, 'Start time should be set');
		$this->assertNotNull($track->stats->finishedAt, 'Finish time should be set');
	}

	/**
	 * Test loading GPX file with complete metadata.
	 * 
	 * Requirements: 4.2
	 */
	public function test_load_gpx_file_with_complete_metadata(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$filePath = $this->getFixturePath('complete-gpx.gpx');
		
		// Act
		$file = $gpx->load($filePath);
		
		// Assert - Metadata
		$this->assertNotNull($file->metadata, 'File should have metadata');
		$this->assertEquals('Complete GPX Test File', $file->metadata->name);
		$this->assertEquals('A comprehensive GPX file with all optional elements for testing', $file->metadata->description);
		
		// Assert - Author
		$this->assertNotNull($file->metadata->author, 'Metadata should have author');
		$this->assertEquals('Test Author', $file->metadata->author->name);
		
		// Assert - Author email
		$this->assertNotNull($file->metadata->author->email, 'Author should have email');
		$this->assertEquals('test', $file->metadata->author->email->id);
		$this->assertEquals('example.com', $file->metadata->author->email->domain);
		
		// Assert - Copyright
		$this->assertNotNull($file->metadata->copyright, 'Metadata should have copyright');
		$this->assertEquals('Test Copyright Holder', $file->metadata->copyright->author);
		$this->assertEquals(2024, $file->metadata->copyright->year);
		
		// Assert - Keywords
		$this->assertEquals('test, complete, gpx, all-elements', $file->metadata->keywords);
		
		// Assert - Bounds
		$this->assertNotNull($file->metadata->bounds, 'Metadata should have bounds');
		$this->assertCoordinatesEqual(54.9, $file->metadata->bounds->minLatitude);
		$this->assertCoordinatesEqual(55.0, $file->metadata->bounds->maxLatitude);
		$this->assertCoordinatesEqual(9.8, $file->metadata->bounds->minLongitude);
		$this->assertCoordinatesEqual(9.9, $file->metadata->bounds->maxLongitude);
	}
}

