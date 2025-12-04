<?php

declare(strict_types=1);

namespace phpGPX\Tests\Integration;

use phpGPX\phpGPX;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use phpGPX\Tests\Support\TestCase;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\TrackFactory;
use phpGPX\Tests\Support\Factories\MetadataFactory;

/**
 * Integration tests for round-trip consistency.
 * 
 * Tests Requirements 4.4
 */
final class RoundTripTest extends TestCase
{
	/**
	 * Test loading and immediately saving preserves data.
	 * 
	 * Requirements: 4.4
	 */
	public function test_load_and_save_preserves_data(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$originalFilePath = $this->getFixturePath('complete-gpx.gpx');
		
		// Act - Load the file
		$loadedFile = $gpx->load($originalFilePath);
		
		// Save to temporary file
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_roundtrip_test_');
		$loadedFile->save($tempFile, phpGPX::XML_FORMAT);
		
		// Load the saved file
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Metadata preserved
		$this->assertNotNull($reloadedFile->metadata, 'Metadata should be preserved');
		$this->assertEquals(
			$loadedFile->metadata->name,
			$reloadedFile->metadata->name,
			'Metadata name should be preserved'
		);
		$this->assertEquals(
			$loadedFile->metadata->description,
			$reloadedFile->metadata->description,
			'Metadata description should be preserved'
		);
		
		// Assert - Waypoints preserved
		$this->assertCount(
			count($loadedFile->waypoints),
			$reloadedFile->waypoints,
			'Number of waypoints should be preserved'
		);
		
		if (!empty($loadedFile->waypoints)) {
			$originalWaypoint = $loadedFile->waypoints[0];
			$reloadedWaypoint = $reloadedFile->waypoints[0];
			
			$this->assertEquals($originalWaypoint->name, $reloadedWaypoint->name);
			$this->assertEquals($originalWaypoint->description, $reloadedWaypoint->description);
			$this->assertCoordinatesEqual($originalWaypoint->latitude, $reloadedWaypoint->latitude);
			$this->assertCoordinatesEqual($originalWaypoint->longitude, $reloadedWaypoint->longitude);
			$this->assertCoordinatesEqual($originalWaypoint->elevation, $reloadedWaypoint->elevation);
		}
		
		// Assert - Routes preserved
		$this->assertCount(
			count($loadedFile->routes),
			$reloadedFile->routes,
			'Number of routes should be preserved'
		);
		
		if (!empty($loadedFile->routes)) {
			$originalRoute = $loadedFile->routes[0];
			$reloadedRoute = $reloadedFile->routes[0];
			
			$this->assertEquals($originalRoute->name, $reloadedRoute->name);
			$this->assertEquals($originalRoute->description, $reloadedRoute->description);
			$this->assertCount(
				count($originalRoute->points),
				$reloadedRoute->points,
				'Number of route points should be preserved'
			);
		}
		
		// Assert - Tracks preserved
		$this->assertCount(
			count($loadedFile->tracks),
			$reloadedFile->tracks,
			'Number of tracks should be preserved'
		);
		
		if (!empty($loadedFile->tracks)) {
			$originalTrack = $loadedFile->tracks[0];
			$reloadedTrack = $reloadedFile->tracks[0];
			
			$this->assertEquals($originalTrack->name, $reloadedTrack->name);
			$this->assertEquals($originalTrack->description, $reloadedTrack->description);
			$this->assertCount(
				count($originalTrack->segments),
				$reloadedTrack->segments,
				'Number of segments should be preserved'
			);
			
			// Check first segment
			if (!empty($originalTrack->segments)) {
				$originalSegment = $originalTrack->segments[0];
				$reloadedSegment = $reloadedTrack->segments[0];
				
				$this->assertCount(
					count($originalSegment->points),
					$reloadedSegment->points,
					'Number of points in segment should be preserved'
				);
				
				// Check first point
				if (!empty($originalSegment->points)) {
					$originalPoint = $originalSegment->points[0];
					$reloadedPoint = $reloadedSegment->points[0];
					
					$this->assertCoordinatesEqual($originalPoint->latitude, $reloadedPoint->latitude);
					$this->assertCoordinatesEqual($originalPoint->longitude, $reloadedPoint->longitude);
					$this->assertCoordinatesEqual($originalPoint->elevation, $reloadedPoint->elevation);
				}
			}
		}
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test loading GPX file, modifying it, and saving preserves modifications.
	 * 
	 * Requirements: 4.4
	 */
	public function test_load_modify_and_save_preserves_modifications(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$originalFilePath = $this->getFixturePath('minimal-gpx.gpx');
		
		// Act - Load the file
		$loadedFile = $gpx->load($originalFilePath);
		
		// Modify metadata
		$loadedFile->metadata->name = 'Modified GPX File';
		$loadedFile->metadata->description = 'This file has been modified';
		
		// Modify track name
		$loadedFile->tracks[0]->name = 'Modified Track Name';
		$loadedFile->tracks[0]->description = 'Modified track description';
		
		// Add a new waypoint
		$newWaypoint = PointFactory::create([
			'pointType' => Point::WAYPOINT,
			'latitude' => 55.0,
			'longitude' => 10.0,
			'elevation' => 100.0,
			'name' => 'New Waypoint',
			'description' => 'Added during modification',
		]);
		$loadedFile->waypoints[] = $newWaypoint;
		
		// Save to temporary file
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_modify_test_');
		$loadedFile->save($tempFile, phpGPX::XML_FORMAT);
		
		// Load the saved file
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Modifications preserved
		$this->assertEquals('Modified GPX File', $reloadedFile->metadata->name);
		$this->assertEquals('This file has been modified', $reloadedFile->metadata->description);
		$this->assertEquals('Modified Track Name', $reloadedFile->tracks[0]->name);
		$this->assertEquals('Modified track description', $reloadedFile->tracks[0]->description);
		
		// Assert - New waypoint preserved
		$this->assertCount(1, $reloadedFile->waypoints, 'Should have the new waypoint');
		$this->assertEquals('New Waypoint', $reloadedFile->waypoints[0]->name);
		$this->assertEquals('Added during modification', $reloadedFile->waypoints[0]->description);
		$this->assertCoordinatesEqual(55.0, $reloadedFile->waypoints[0]->latitude);
		$this->assertCoordinatesEqual(10.0, $reloadedFile->waypoints[0]->longitude);
		$this->assertCoordinatesEqual(100.0, $reloadedFile->waypoints[0]->elevation);
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test round-trip with extensions preserves extension data.
	 * 
	 * Requirements: 4.4
	 */
	public function test_round_trip_preserves_extensions(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$originalFilePath = $this->getFixturePath('track-with-extensions.gpx');
		
		// Act - Load, save, and reload
		$loadedFile = $gpx->load($originalFilePath);
		
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_extensions_test_');
		$loadedFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Extensions preserved
		$this->assertNotEmpty($reloadedFile->tracks, 'Should have tracks');
		$track = $reloadedFile->tracks[0];
		
		$this->assertNotEmpty($track->segments, 'Should have segments');
		$segment = $track->segments[0];
		
		$this->assertNotEmpty($segment->points, 'Should have points');
		$point = $segment->points[0];
		
		$this->assertNotNull($point->extensions, 'Point should have extensions');
		$this->assertNotEmpty($point->extensions->trackPointExtension, 'Should have TrackPointExtension');
		
		$extension = $point->extensions->trackPointExtension;
		$this->assertEquals(120, $extension->heartRate, 'Heart rate should be preserved');
		$this->assertEquals(85, $extension->cadence, 'Cadence should be preserved');
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test round-trip with multiple tracks and segments.
	 * 
	 * Requirements: 4.4
	 */
	public function test_round_trip_with_multiple_tracks_and_segments(): void
	{
		// Arrange - Create GPX with multiple tracks and segments
		$gpxFile = new GpxFile();
		$gpxFile->creator = 'phpGPX Round-Trip Test';
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Multi-Track Test']);
		
		// Add first track with 2 segments
		$track1 = TrackFactory::createWithSegments(2, 3);
		$track1->name = 'Track 1';
		$gpxFile->tracks[] = $track1;
		
		// Add second track with 1 segment
		$track2 = TrackFactory::createWithSegments(1, 4);
		$track2->name = 'Track 2';
		$gpxFile->tracks[] = $track2;
		
		// Act - Save and reload
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_multi_track_test_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Structure preserved
		$this->assertCount(2, $reloadedFile->tracks, 'Should have 2 tracks');
		
		// Check first track
		$this->assertEquals('Track 1', $reloadedFile->tracks[0]->name);
		$this->assertCount(2, $reloadedFile->tracks[0]->segments, 'Track 1 should have 2 segments');
		$this->assertCount(3, $reloadedFile->tracks[0]->segments[0]->points, 'Segment 1 should have 3 points');
		$this->assertCount(3, $reloadedFile->tracks[0]->segments[1]->points, 'Segment 2 should have 3 points');
		
		// Check second track
		$this->assertEquals('Track 2', $reloadedFile->tracks[1]->name);
		$this->assertCount(1, $reloadedFile->tracks[1]->segments, 'Track 2 should have 1 segment');
		$this->assertCount(4, $reloadedFile->tracks[1]->segments[0]->points, 'Segment should have 4 points');
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test round-trip preserves coordinate precision.
	 * 
	 * Requirements: 4.4
	 */
	public function test_round_trip_preserves_coordinate_precision(): void
	{
		// Arrange - Create GPX with high-precision coordinates
		$gpxFile = new GpxFile();
		$gpxFile->metadata = MetadataFactory::create(['name' => 'Precision Test']);
		
		$track = TrackFactory::create();
		$segment = new \phpGPX\Models\Segment();
		
		// Add point with high precision
		$highPrecisionLat = 54.93286210888934567;
		$highPrecisionLon = 9.86062421614008345;
		$highPrecisionEle = 42.567890123;
		
		$point = PointFactory::create([
			'latitude' => $highPrecisionLat,
			'longitude' => $highPrecisionLon,
			'elevation' => $highPrecisionEle,
		]);
		$segment->points[] = $point;
		
		$track->segments[] = $segment;
		$gpxFile->tracks[] = $track;
		
		// Act - Save and reload
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_precision_test_');
		$gpxFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$gpx = new phpGPX();
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Precision preserved (within reasonable tolerance)
		$reloadedPoint = $reloadedFile->tracks[0]->segments[0]->points[0];
		$this->assertCoordinatesEqual($highPrecisionLat, $reloadedPoint->latitude, 0.000001);
		$this->assertCoordinatesEqual($highPrecisionLon, $reloadedPoint->longitude, 0.000001);
		$this->assertCoordinatesEqual($highPrecisionEle, $reloadedPoint->elevation, 0.001);
		
		// Cleanup
		unlink($tempFile);
	}

	/**
	 * Test round-trip with metadata including author and copyright.
	 * 
	 * Requirements: 4.4
	 */
	public function test_round_trip_preserves_complete_metadata(): void
	{
		// Arrange
		$gpx = new phpGPX();
		$originalFilePath = $this->getFixturePath('complete-gpx.gpx');
		
		// Act - Load, save, and reload
		$loadedFile = $gpx->load($originalFilePath);
		
		$tempFile = tempnam(sys_get_temp_dir(), 'gpx_metadata_test_');
		$loadedFile->save($tempFile, phpGPX::XML_FORMAT);
		
		$reloadedFile = $gpx->load($tempFile);
		
		// Assert - Metadata preserved
		$this->assertNotNull($reloadedFile->metadata, 'Metadata should exist');
		$this->assertEquals($loadedFile->metadata->name, $reloadedFile->metadata->name);
		$this->assertEquals($loadedFile->metadata->description, $reloadedFile->metadata->description);
		$this->assertEquals($loadedFile->metadata->keywords, $reloadedFile->metadata->keywords);
		
		// Assert - Author preserved
		$this->assertNotNull($reloadedFile->metadata->author, 'Author should be preserved');
		$this->assertEquals($loadedFile->metadata->author->name, $reloadedFile->metadata->author->name);
		
		// Assert - Author email preserved
		if ($loadedFile->metadata->author->email !== null) {
			$this->assertNotNull($reloadedFile->metadata->author->email, 'Author email should be preserved');
			$this->assertEquals(
				$loadedFile->metadata->author->email->id,
				$reloadedFile->metadata->author->email->id
			);
			$this->assertEquals(
				$loadedFile->metadata->author->email->domain,
				$reloadedFile->metadata->author->email->domain
			);
		}
		
		// Assert - Copyright preserved
		if ($loadedFile->metadata->copyright !== null) {
			$this->assertNotNull($reloadedFile->metadata->copyright, 'Copyright should be preserved');
			$this->assertEquals(
				$loadedFile->metadata->copyright->author,
				$reloadedFile->metadata->copyright->author
			);
			$this->assertEquals(
				$loadedFile->metadata->copyright->year,
				$reloadedFile->metadata->copyright->year
			);
		}
		
		// Assert - Bounds preserved
		if ($loadedFile->metadata->bounds !== null) {
			$this->assertNotNull($reloadedFile->metadata->bounds, 'Bounds should be preserved');
			$this->assertCoordinatesEqual(
				$loadedFile->metadata->bounds->minLatitude,
				$reloadedFile->metadata->bounds->minLatitude
			);
			$this->assertCoordinatesEqual(
				$loadedFile->metadata->bounds->maxLatitude,
				$reloadedFile->metadata->bounds->maxLatitude
			);
			$this->assertCoordinatesEqual(
				$loadedFile->metadata->bounds->minLongitude,
				$reloadedFile->metadata->bounds->minLongitude
			);
			$this->assertCoordinatesEqual(
				$loadedFile->metadata->bounds->maxLongitude,
				$reloadedFile->metadata->bounds->maxLongitude
			);
		}
		
		// Cleanup
		unlink($tempFile);
	}
}
