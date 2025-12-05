<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use Eris\Generators;
use Eris\TestTrait;
use phpGPX\Models\Collection;
use phpGPX\Models\Extensions;
use phpGPX\Models\Link;
use phpGPX\Models\Route;
use phpGPX\Models\Stats;
use phpGPX\Models\Track;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\Factories\SegmentFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Collection model (abstract base class).
 * Tests common functionality through concrete implementations (Track and Route).
 */
final class CollectionTest extends TestCase
{
	use TestTrait;
	public function test_collection_initializes_with_default_values(): void
	{
		// Arrange & Act - using Track as concrete implementation
		$collection = new Track();
		
		// Assert
		$this->assertNull($collection->name);
		$this->assertNull($collection->comment);
		$this->assertNull($collection->description);
		$this->assertNull($collection->source);
		$this->assertIsArray($collection->links);
		$this->assertEmpty($collection->links);
		$this->assertNull($collection->number);
		$this->assertNull($collection->type);
		$this->assertNull($collection->extensions);
	}

	public function test_collection_stores_name_property(): void
	{
		// Arrange
		$collection = new Track();
		
		// Act
		$collection->name = 'Test Collection Name';
		
		// Assert
		$this->assertEquals('Test Collection Name', $collection->name);
	}

	public function test_collection_stores_comment_property(): void
	{
		// Arrange
		$collection = new Route();
		
		// Act
		$collection->comment = 'This is a test comment';
		
		// Assert
		$this->assertEquals('This is a test comment', $collection->comment);
	}

	public function test_collection_stores_description_property(): void
	{
		// Arrange
		$collection = new Track();
		
		// Act
		$collection->description = 'A detailed description of the collection';
		
		// Assert
		$this->assertEquals('A detailed description of the collection', $collection->description);
	}

	public function test_collection_stores_source_property(): void
	{
		// Arrange
		$collection = new Route();
		
		// Act
		$collection->source = 'Garmin eTrex 30';
		
		// Assert
		$this->assertEquals('Garmin eTrex 30', $collection->source);
	}

	public function test_collection_stores_number_property(): void
	{
		// Arrange
		$collection = new Track();
		
		// Act
		$collection->number = 42;
		
		// Assert
		$this->assertEquals(42, $collection->number);
	}

	public function test_collection_stores_type_property(): void
	{
		// Arrange
		$collection = new Route();
		
		// Act
		$collection->type = 'hiking';
		
		// Assert
		$this->assertEquals('hiking', $collection->type);
	}

	public function test_collection_can_add_single_link(): void
	{
		// Arrange
		$collection = new Track();
		$link = new Link('https://example.com', 'Example Link');
		
		// Act
		$collection->links[] = $link;
		
		// Assert
		$this->assertCount(1, $collection->links);
		$this->assertSame($link, $collection->links[0]);
		$this->assertEquals('https://example.com', $collection->links[0]->href);
		$this->assertEquals('Example Link', $collection->links[0]->text);
	}

	public function test_collection_can_add_multiple_links(): void
	{
		// Arrange
		$collection = new Route();
		$link1 = new Link('https://example.com/1', 'Link 1');
		
		$link2 = new Link('https://example.com/2', 'Link 2');
		
		$link3 = new Link('https://example.com/3', 'Link 3');
		
		// Act
		$collection->links[] = $link1;
		$collection->links[] = $link2;
		$collection->links[] = $link3;
		
		// Assert
		$this->assertCount(3, $collection->links);
		$this->assertSame($link1, $collection->links[0]);
		$this->assertSame($link2, $collection->links[1]);
		$this->assertSame($link3, $collection->links[2]);
	}

	public function test_collection_can_remove_link(): void
	{
		// Arrange
		$collection = new Track();
		$link1 = new Link('https://example.com/1');
		$link2 = new Link('https://example.com/2');
		
		$collection->links[] = $link1;
		$collection->links[] = $link2;
		
		// Act
		unset($collection->links[0]);
		$collection->links = array_values($collection->links); // Re-index
		
		// Assert
		$this->assertCount(1, $collection->links);
		$this->assertSame($link2, $collection->links[0]);
	}

	public function test_collection_links_array_is_iterable(): void
	{
		// Arrange
		$collection = new Route();
		$link1 = new Link('https://example.com/1');
		$link2 = new Link('https://example.com/2');
		
		$collection->links[] = $link1;
		$collection->links[] = $link2;
		
		// Act
		$hrefs = [];
		foreach ($collection->links as $link) {
			$hrefs[] = $link->href;
		}
		
		// Assert
		$this->assertEquals(['https://example.com/1', 'https://example.com/2'], $hrefs);
	}

	public function test_collection_stores_extensions(): void
	{
		// Arrange
		$collection = new Track();
		$extensions = new Extensions();
		
		// Act
		$collection->extensions = $extensions;
		
		// Assert
		$this->assertInstanceOf(Extensions::class, $collection->extensions);
		$this->assertSame($extensions, $collection->extensions);
	}

	public function test_collection_stores_stats(): void
	{
		// Arrange
		$collection = new Route();
		$stats = new Stats();
		$stats->distance = 1000.0;
		
		// Act
		$collection->stats = $stats;
		
		// Assert
		$this->assertInstanceOf(Stats::class, $collection->stats);
		$this->assertSame($stats, $collection->stats);
		$this->assertEquals(1000.0, $collection->stats->distance);
	}

	public function test_collection_implements_summarizable_interface(): void
	{
		// Arrange & Act
		$collection = new Track();
		
		// Assert
		$this->assertInstanceOf(\phpGPX\Models\Summarizable::class, $collection);
	}

	public function test_collection_implements_stats_calculator_interface(): void
	{
		// Arrange & Act
		$collection = new Route();
		
		// Assert
		$this->assertInstanceOf(\phpGPX\Models\StatsCalculator::class, $collection);
	}

	public function test_collection_get_points_is_abstract_method(): void
	{
		// This test verifies that getPoints() is properly implemented in concrete classes
		
		// Test with Track
		$track = new Track();
		$segment = SegmentFactory::createWithPoints(3);
		$track->segments[] = $segment;
		
		$trackPoints = $track->getPoints();
		$this->assertIsArray($trackPoints);
		$this->assertCount(3, $trackPoints);
		
		// Test with Route
		$route = new Route();
		$route->points[] = PointFactory::create();
		$route->points[] = PointFactory::create();
		
		$routePoints = $route->getPoints();
		$this->assertIsArray($routePoints);
		$this->assertCount(2, $routePoints);
	}

	public function test_track_serialization_includes_collection_properties(): void
	{
		// Arrange
		$track = new Track();
		$track->name = 'Serialization Test';
		$track->comment = 'Test comment';
		$track->description = 'Test description';
		$track->source = 'Test source';
		$track->number = 99;
		$track->type = 'running';
		
		$link = new Link('https://example.com');
		$track->links[] = $link;
		
		// Act
		$array = $track->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('name', $array);
		$this->assertArrayHasKey('cmt', $array);
		$this->assertArrayHasKey('desc', $array);
		$this->assertArrayHasKey('src', $array);
		$this->assertArrayHasKey('number', $array);
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('link', $array);
		$this->assertArrayHasKey('extensions', $array);
		$this->assertArrayHasKey('stats', $array);
		
		$this->assertEquals('Serialization Test', $array['name']);
		$this->assertEquals('Test comment', $array['cmt']);
		$this->assertEquals('Test description', $array['desc']);
		$this->assertEquals('Test source', $array['src']);
		$this->assertEquals(99, $array['number']);
		$this->assertEquals('running', $array['type']);
		$this->assertIsArray($array['link']);
		$this->assertCount(1, $array['link']);
	}

	public function test_route_serialization_includes_collection_properties(): void
	{
		// Arrange
		$route = new Route();
		$route->name = 'Route Serialization Test';
		$route->comment = 'Route comment';
		$route->description = 'Route description';
		$route->source = 'Route source';
		$route->number = 42;
		$route->type = 'cycling';
		
		// Act
		$array = $route->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('name', $array);
		$this->assertArrayHasKey('cmt', $array);
		$this->assertArrayHasKey('desc', $array);
		$this->assertArrayHasKey('src', $array);
		$this->assertArrayHasKey('number', $array);
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('link', $array);
		$this->assertArrayHasKey('extensions', $array);
		$this->assertArrayHasKey('stats', $array);
		
		$this->assertEquals('Route Serialization Test', $array['name']);
		$this->assertEquals('Route comment', $array['cmt']);
		$this->assertEquals('Route description', $array['desc']);
		$this->assertEquals('Route source', $array['src']);
		$this->assertEquals(42, $array['number']);
		$this->assertEquals('cycling', $array['type']);
	}

	public function test_collection_serialization_handles_null_values(): void
	{
		// Arrange
		$collection = new Track();
		// Leave all properties as null (default state)
		
		// Act
		$array = $collection->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertNull($array['name']);
		$this->assertNull($array['cmt']);
		$this->assertNull($array['desc']);
		$this->assertNull($array['src']);
		$this->assertNull($array['number']);
		$this->assertNull($array['type']);
		$this->assertNull($array['extensions']);
		$this->assertNull($array['stats']);
	}

	public function test_collection_with_all_properties_set(): void
	{
		// Arrange
		$collection = new Route();
		$collection->name = 'Complete Collection';
		$collection->comment = 'Full comment';
		$collection->description = 'Full description';
		$collection->source = 'GPS Device';
		$collection->number = 123;
		$collection->type = 'hiking';
		
		$link = new Link('https://example.com');
		$collection->links[] = $link;
		
		$extensions = new Extensions();
		$collection->extensions = $extensions;
		
		$stats = new Stats();
		$collection->stats = $stats;
		
		// Act & Assert - verify all properties are accessible
		$this->assertEquals('Complete Collection', $collection->name);
		$this->assertEquals('Full comment', $collection->comment);
		$this->assertEquals('Full description', $collection->description);
		$this->assertEquals('GPS Device', $collection->source);
		$this->assertEquals(123, $collection->number);
		$this->assertEquals('hiking', $collection->type);
		$this->assertCount(1, $collection->links);
		$this->assertInstanceOf(Extensions::class, $collection->extensions);
		$this->assertInstanceOf(Stats::class, $collection->stats);
	}

	public function test_collection_links_can_be_cleared(): void
	{
		// Arrange
		$collection = new Track();
		$collection->links[] = new Link('https://example.com/1');
		$collection->links[] = new Link('https://example.com/2');
		$this->assertCount(2, $collection->links);
		
		// Act
		$collection->links = [];
		
		// Assert
		$this->assertIsArray($collection->links);
		$this->assertEmpty($collection->links);
	}

	public function test_collection_properties_can_be_updated(): void
	{
		// Arrange
		$collection = new Route();
		$collection->name = 'Original Name';
		$collection->number = 1;
		
		// Act
		$collection->name = 'Updated Name';
		$collection->number = 2;
		
		// Assert
		$this->assertEquals('Updated Name', $collection->name);
		$this->assertEquals(2, $collection->number);
	}

	public function test_collection_properties_can_be_set_to_null(): void
	{
		// Arrange
		$collection = new Track();
		$collection->name = 'Test Name';
		$collection->number = 42;
		$collection->type = 'running';
		
		// Act
		$collection->name = null;
		$collection->number = null;
		$collection->type = null;
		
		// Assert
		$this->assertNull($collection->name);
		$this->assertNull($collection->number);
		$this->assertNull($collection->type);
	}

	/**
	 * Property test: Collection count invariant.
	 * 
	 * **Feature: test-coverage, Property 10: Collection count invariant**
	 * **Validates: Requirements 5.4**
	 */
	public function test_property_collection_count_invariant(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::choose(0, 10), // Initial number of segments for Track
				Generators::choose(1, 5),  // Number of segments to add
				Generators::choose(0, 10), // Initial number of points for Route
				Generators::choose(1, 5)   // Number of points to add
			)
			->withMaxSize(100)
			->then(function ($initialSegments, $segmentsToAdd, $initialPoints, $pointsToAdd) {
				// Test with Track (adding segments)
				$track = new Track();
				
				// Add initial segments
				for ($i = 0; $i < $initialSegments; $i++) {
					$track->segments[] = SegmentFactory::createWithPoints(2);
				}
				
				$originalSegmentCount = count($track->segments);
				
				// Add segments one by one and verify count increases by 1 each time
				for ($i = 0; $i < $segmentsToAdd; $i++) {
					$expectedCount = $originalSegmentCount + $i + 1;
					$track->segments[] = SegmentFactory::createWithPoints(2);
					
					$this->assertEquals(
						$expectedCount,
						count($track->segments),
						"Adding segment should increase count by exactly 1"
					);
				}
				
				// Test with Route (adding points)
				$route = new Route();
				
				// Add initial points
				for ($i = 0; $i < $initialPoints; $i++) {
					$route->points[] = PointFactory::create();
				}
				
				$originalPointCount = count($route->points);
				
				// Add points one by one and verify count increases by 1 each time
				for ($i = 0; $i < $pointsToAdd; $i++) {
					$expectedCount = $originalPointCount + $i + 1;
					$route->points[] = PointFactory::create();
					
					$this->assertEquals(
						$expectedCount,
						count($route->points),
						"Adding point should increase count by exactly 1"
					);
				}
				
				// Test with links (common to all collections)
				$collection = new Track();
				
				// Add initial links
				$initialLinkCount = mt_rand(0, 5);
				for ($i = 0; $i < $initialLinkCount; $i++) {
					$link = new Link("https://example.com/{$i}");
					$collection->links[] = $link;
				}
				
				$originalLinkCount = count($collection->links);
				
				// Add links one by one and verify count increases by 1 each time
				$linksToAdd = mt_rand(1, 3);
				for ($i = 0; $i < $linksToAdd; $i++) {
					$expectedCount = $originalLinkCount + $i + 1;
					$link = new Link("https://example.com/new-{$i}");
					$collection->links[] = $link;
					
					$this->assertEquals(
						$expectedCount,
						count($collection->links),
						"Adding link should increase count by exactly 1"
					);
				}
			});
	}
}
