<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Link;
use phpGPX\Models\Point;
use phpGPX\Models\Route;
use phpGPX\Models\Stats;
use phpGPX\Parsers\RouteParser;
use phpGPX\Tests\Support\Factories\PointFactory;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Route model.
 */
final class RouteTest extends TestCase
{
	public function test_route_can_be_created(): void
	{
		// Arrange & Act
		$route = new Route();
		
		// Assert
		$this->assertInstanceOf(Route::class, $route);
		$this->assertIsArray($route->points);
		$this->assertEmpty($route->points);
		$this->assertIsArray($route->links);
		$this->assertEmpty($route->links);
	}

	public function test_route_stores_basic_properties(): void
	{
		// Arrange
		$route = new Route();
		
		// Act
		$route->name = 'City Tour';
		$route->comment = 'Nice route';
		$route->description = 'A scenic tour through the city';
		$route->source = 'GPS Device';
		$route->number = 5;
		$route->type = 'walking';
		
		// Assert
		$this->assertEquals('City Tour', $route->name);
		$this->assertEquals('Nice route', $route->comment);
		$this->assertEquals('A scenic tour through the city', $route->description);
		$this->assertEquals('GPS Device', $route->source);
		$this->assertEquals(5, $route->number);
		$this->assertEquals('walking', $route->type);
	}

	public function test_route_can_add_points(): void
	{
		// Arrange
		$route = new Route();
		$point1 = PointFactory::create();
		$point2 = PointFactory::create();
		
		// Act
		$route->points[] = $point1;
		$route->points[] = $point2;
		
		// Assert
		$this->assertCount(2, $route->points);
		$this->assertSame($point1, $route->points[0]);
		$this->assertSame($point2, $route->points[1]);
	}

	public function test_route_can_add_links(): void
	{
		// Arrange
		$route = new Route();
		$link = new Link('https://example.com/route', 'Route Info');
		
		// Act
		$route->links[] = $link;
		
		// Assert
		$this->assertCount(1, $route->links);
		$this->assertEquals('https://example.com/route', $route->links[0]->href);
		$this->assertEquals('Route Info', $route->links[0]->text);
	}

	public function test_get_points_returns_all_points(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 5; $i++) {
			$route->points[] = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
			]);
		}
		
		// Act
		$points = $route->getPoints();
		
		// Assert
		$this->assertCount(5, $points);
		$this->assertContainsOnlyInstancesOf(Point::class, $points);
	}

	public function test_get_points_returns_empty_array_when_no_points(): void
	{
		// Arrange
		$route = new Route();
		
		// Act
		$points = $route->getPoints();
		
		// Assert
		$this->assertIsArray($points);
		$this->assertEmpty($points);
	}

	public function test_recalculate_stats_creates_stats_object(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 5; $i++) {
			$route->points[] = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
			]);
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $route->stats);
	}

	public function test_recalculate_stats_calculates_distance(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 10; $i++) {
			$route->points[] = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
			]);
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertIsFloat($route->stats->distance);
		$this->assertGreaterThan(0, $route->stats->distance);
	}

	public function test_recalculate_stats_with_empty_route(): void
	{
		// Arrange
		$route = new Route();
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $route->stats);
		$this->assertEquals(0.0, $route->stats->distance);
	}

	public function test_recalculate_stats_with_single_point(): void
	{
		// Arrange
		$route = new Route();
		$route->points[] = PointFactory::create();
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(Stats::class, $route->stats);
		$this->assertNotNull($route->stats->startedAtCoords);
		$this->assertNotNull($route->stats->finishedAtCoords);
		$this->assertEquals($route->stats->startedAtCoords, $route->stats->finishedAtCoords);
	}

	public function test_recalculate_stats_with_elevation_data(): void
	{
		// Arrange
		$route = new Route();
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'elevation' => 100.0 + ($i * 10.0), // Ascending elevation
			]);
			$route->points[] = $point;
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertNotNull($route->stats->cumulativeElevationGain);
		$this->assertGreaterThan(0, $route->stats->cumulativeElevationGain);
		$this->assertNotNull($route->stats->minAltitude);
		$this->assertNotNull($route->stats->maxAltitude);
		$this->assertGreaterThan($route->stats->minAltitude, $route->stats->maxAltitude);
	}

	public function test_recalculate_stats_with_time_data(): void
	{
		// Arrange
		$route = new Route();
		$baseTime = new \DateTime('2024-01-01 10:00:00');
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'time' => (clone $baseTime)->modify("+{$i} minutes"),
			]);
			$route->points[] = $point;
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $route->stats->startedAt);
		$this->assertInstanceOf(\DateTime::class, $route->stats->finishedAt);
		$this->assertNotNull($route->stats->duration);
		$this->assertEquals(540, $route->stats->duration); // 9 minutes = 540 seconds
		$this->assertNotNull($route->stats->averageSpeed);
		$this->assertGreaterThan(0, $route->stats->averageSpeed);
	}

	public function test_recalculate_stats_finds_min_and_max_altitude(): void
	{
		// Arrange
		$route = new Route();
		
		// Add points with varying elevations
		$route->points[] = PointFactory::create(['elevation' => 150.0]);
		$route->points[] = PointFactory::create(['elevation' => 200.0]); // Max
		$route->points[] = PointFactory::create(['elevation' => 100.0]); // Min
		$route->points[] = PointFactory::create(['elevation' => 175.0]);
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertEquals(100.0, $route->stats->minAltitude);
		$this->assertEquals(200.0, $route->stats->maxAltitude);
		$this->assertNotNull($route->stats->minAltitudeCoords);
		$this->assertNotNull($route->stats->maxAltitudeCoords);
	}

	public function test_to_array_serialization(): void
	{
		// Arrange
		$route = new Route();
		$route->name = 'Test Route';
		$route->description = 'Test Description';
		$route->number = 1;
		$route->type = 'cycling';
		
		// Act
		$array = $route->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('name', $array);
		$this->assertArrayHasKey('desc', $array);
		$this->assertArrayHasKey('number', $array);
		$this->assertArrayHasKey('type', $array);
		$this->assertArrayHasKey('rtep', $array);
		$this->assertArrayHasKey('stats', $array);
		$this->assertEquals('Test Route', $array['name']);
		$this->assertEquals('Test Description', $array['desc']);
		$this->assertEquals(1, $array['number']);
		$this->assertEquals('cycling', $array['type']);
	}

	public function test_to_array_includes_points(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 3; $i++) {
			$route->points[] = PointFactory::create();
		}
		
		// Act
		$array = $route->toArray();
		
		// Assert
		$this->assertArrayHasKey('rtep', $array);
		$this->assertIsArray($array['rtep']);
		$this->assertCount(3, $array['rtep']);
	}

	public function test_to_array_includes_stats_after_calculation(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 5; $i++) {
			$route->points[] = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
			]);
		}
		$route->recalculateStats();
		
		// Act
		$array = $route->toArray();
		
		// Assert
		$this->assertArrayHasKey('stats', $array);
		$this->assertIsArray($array['stats']);
		$this->assertArrayHasKey('distance', $array['stats']);
	}

	public function test_to_xml_serialization(): void
	{
		// Arrange
		$route = new Route();
		$route->name = 'XML Test Route';
		for ($i = 0; $i < 3; $i++) {
			$route->points[] = PointFactory::create(['pointType' => Point::ROUTEPOINT]);
		}
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = RouteParser::toXML($route, $document);
		
		// Assert
		$this->assertInstanceOf(\DOMElement::class, $xmlNode);
		$this->assertEquals('rte', $xmlNode->nodeName);
		
		// Check for name element
		$nameNodes = $xmlNode->getElementsByTagName('name');
		$this->assertGreaterThan(0, $nameNodes->length);
		$this->assertEquals('XML Test Route', $nameNodes->item(0)->nodeValue);
		
		// Check for route point elements
		$pointNodes = $xmlNode->getElementsByTagName('rtept');
		$this->assertEquals(3, $pointNodes->length);
	}

	public function test_to_xml_includes_all_properties(): void
	{
		// Arrange
		$route = new Route();
		$route->name = 'Complete Route';
		$route->description = 'Full description';
		$route->comment = 'Test comment';
		$route->source = 'Test source';
		$route->number = 42;
		$route->type = 'hiking';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = RouteParser::toXML($route, $document);
		
		// Assert
		$this->assertEquals('Complete Route', $xmlNode->getElementsByTagName('name')->item(0)->nodeValue);
		$this->assertEquals('Full description', $xmlNode->getElementsByTagName('desc')->item(0)->nodeValue);
		$this->assertEquals('Test comment', $xmlNode->getElementsByTagName('cmt')->item(0)->nodeValue);
		$this->assertEquals('Test source', $xmlNode->getElementsByTagName('src')->item(0)->nodeValue);
		$this->assertEquals('42', $xmlNode->getElementsByTagName('number')->item(0)->nodeValue);
		$this->assertEquals('hiking', $xmlNode->getElementsByTagName('type')->item(0)->nodeValue);
	}

	public function test_to_xml_with_empty_route(): void
	{
		// Arrange
		$route = new Route();
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = RouteParser::toXML($route, $document);
		
		// Assert
		$this->assertInstanceOf(\DOMElement::class, $xmlNode);
		$this->assertEquals('rte', $xmlNode->nodeName);
		$pointNodes = $xmlNode->getElementsByTagName('rtept');
		$this->assertEquals(0, $pointNodes->length);
	}

	public function test_to_xml_includes_links(): void
	{
		// Arrange
		$route = new Route();
		$route->name = 'Route with Links';
		
		$link = new Link('https://example.com', 'Example');
		$route->links[] = $link;
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlNode = RouteParser::toXML($route, $document);
		
		// Assert
		$linkNodes = $xmlNode->getElementsByTagName('link');
		$this->assertEquals(1, $linkNodes->length);
	}

	public function test_recalculate_stats_calculates_real_distance(): void
	{
		// Arrange
		$route = new Route();
		
		for ($i = 0; $i < 5; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'elevation' => 100.0 + ($i * 10.0),
			]);
			$route->points[] = $point;
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertNotNull($route->stats->realDistance);
		$this->assertGreaterThan(0, $route->stats->realDistance);
		// Real distance should be >= raw distance due to elevation changes
		$this->assertGreaterThanOrEqual($route->stats->distance, $route->stats->realDistance);
	}

	public function test_recalculate_stats_sets_start_and_finish_coordinates(): void
	{
		// Arrange
		$route = new Route();
		$route->points[] = PointFactory::create(['latitude' => 54.0, 'longitude' => 9.0]);
		$route->points[] = PointFactory::create(['latitude' => 54.1, 'longitude' => 9.1]);
		$route->points[] = PointFactory::create(['latitude' => 54.2, 'longitude' => 9.2]);
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertIsArray($route->stats->startedAtCoords);
		$this->assertIsArray($route->stats->finishedAtCoords);
		$this->assertEquals(['lat' => 54.0, 'lng' => 9.0], $route->stats->startedAtCoords);
		$this->assertEquals(['lat' => 54.2, 'lng' => 9.2], $route->stats->finishedAtCoords);
	}

	public function test_get_points_sorts_by_timestamp_when_enabled(): void
	{
		// Arrange
		$originalSortSetting = \phpGPX\phpGPX::$SORT_BY_TIMESTAMP;
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = true;
		
		$route = new Route();
		
		// Add points with timestamps in reverse order
		$time3 = new \DateTime('2024-01-01 12:00:00');
		$time2 = new \DateTime('2024-01-01 11:00:00');
		$time1 = new \DateTime('2024-01-01 10:00:00');
		
		$route->points[] = PointFactory::create(['time' => $time3, 'latitude' => 54.3]);
		$route->points[] = PointFactory::create(['time' => $time2, 'latitude' => 54.2]);
		$route->points[] = PointFactory::create(['time' => $time1, 'latitude' => 54.1]);
		
		// Act
		$points = $route->getPoints();
		
		// Assert
		$this->assertEquals($time1, $points[0]->time);
		$this->assertEquals($time2, $points[1]->time);
		$this->assertEquals($time3, $points[2]->time);
		
		// Restore original setting
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = $originalSortSetting;
	}

	public function test_get_points_does_not_sort_when_sorting_disabled(): void
	{
		// Arrange
		$originalSortSetting = \phpGPX\phpGPX::$SORT_BY_TIMESTAMP;
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = false;
		
		$route = new Route();
		
		// Add points with timestamps in reverse order
		$time3 = new \DateTime('2024-01-01 12:00:00');
		$time2 = new \DateTime('2024-01-01 11:00:00');
		$time1 = new \DateTime('2024-01-01 10:00:00');
		
		$route->points[] = PointFactory::create(['time' => $time3, 'latitude' => 54.3]);
		$route->points[] = PointFactory::create(['time' => $time2, 'latitude' => 54.2]);
		$route->points[] = PointFactory::create(['time' => $time1, 'latitude' => 54.1]);
		
		// Act
		$points = $route->getPoints();
		
		// Assert - should remain in original order
		$this->assertEquals($time3, $points[0]->time);
		$this->assertEquals($time2, $points[1]->time);
		$this->assertEquals($time1, $points[2]->time);
		
		// Restore original setting
		\phpGPX\phpGPX::$SORT_BY_TIMESTAMP = $originalSortSetting;
	}

	public function test_recalculate_stats_with_null_elevation_values(): void
	{
		// Arrange
		$route = new Route();
		
		// Add points with some null elevations
		$route->points[] = PointFactory::create(['elevation' => 100.0]);
		$route->points[] = PointFactory::create(['elevation' => null]);
		$route->points[] = PointFactory::create(['elevation' => 150.0]);
		
		// Act
		$route->recalculateStats();
		
		// Assert - should handle null elevations gracefully
		$this->assertInstanceOf(Stats::class, $route->stats);
	}

	public function test_recalculate_stats_resets_previous_stats(): void
	{
		// Arrange
		$route = new Route();
		for ($i = 0; $i < 5; $i++) {
			$route->points[] = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
			]);
		}
		$route->recalculateStats();
		$firstDistance = $route->stats->distance;
		
		// Add more points
		$route->points[] = PointFactory::create(['latitude' => 55.0, 'longitude' => 10.0]);
		$route->points[] = PointFactory::create(['latitude' => 55.1, 'longitude' => 10.1]);
		
		// Act
		$route->recalculateStats();
		
		// Assert - distance should be recalculated and different
		$this->assertNotEquals($firstDistance, $route->stats->distance);
		$this->assertGreaterThan($firstDistance, $route->stats->distance);
	}

	public function test_to_array_with_empty_route(): void
	{
		// Arrange
		$route = new Route();
		
		// Act
		$array = $route->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEmpty($array['rtep']);
		$this->assertNull($array['stats']);
	}

	public function test_recalculate_stats_calculates_average_pace(): void
	{
		// Arrange
		$route = new Route();
		$baseTime = new \DateTime('2024-01-01 10:00:00');
		
		for ($i = 0; $i < 10; $i++) {
			$point = PointFactory::create([
				'latitude' => 54.0 + ($i * 0.01),
				'longitude' => 9.0 + ($i * 0.01),
				'time' => (clone $baseTime)->modify("+{$i} minutes"),
			]);
			$route->points[] = $point;
		}
		
		// Act
		$route->recalculateStats();
		
		// Assert
		$this->assertNotNull($route->stats->averagePace);
		$this->assertGreaterThan(0, $route->stats->averagePace);
	}
}
