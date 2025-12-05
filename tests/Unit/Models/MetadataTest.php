<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Bounds;
use phpGPX\Models\Copyright;
use phpGPX\Models\Extensions;
use phpGPX\Models\Link;
use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Metadata model.
 * Tests Metadata creation with various fields and serialization.
 */
final class MetadataTest extends TestCase
{
	/**
	 * Test Metadata can be created with default values.
	 * Requirements: 2.2
	 */
	public function test_metadata_can_be_created_with_default_values(): void
	{
		// Arrange & Act
		$metadata = new Metadata();
		
		// Assert
		$this->assertInstanceOf(Metadata::class, $metadata);
		$this->assertNull($metadata->name);
		$this->assertNull($metadata->description);
		$this->assertNull($metadata->author);
		$this->assertNull($metadata->copyright);
		$this->assertIsArray($metadata->links);
		$this->assertEmpty($metadata->links);
		$this->assertNull($metadata->time);
		$this->assertNull($metadata->keywords);
		$this->assertNull($metadata->bounds);
		$this->assertNull($metadata->extensions);
	}

	/**
	 * Test Metadata with name and description.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_name_and_description(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$metadata->name = 'My GPX Track';
		$metadata->description = 'A beautiful hiking trail';
		
		// Assert
		$this->assertEquals('My GPX Track', $metadata->name);
		$this->assertEquals('A beautiful hiking trail', $metadata->description);
	}

	/**
	 * Test Metadata with author.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_author(): void
	{
		// Arrange
		$metadata = new Metadata();
		$author = new Person();
		$author->name = 'John Doe';
		
		// Act
		$metadata->author = $author;
		
		// Assert
		$this->assertInstanceOf(Person::class, $metadata->author);
		$this->assertEquals('John Doe', $metadata->author->name);
	}

	/**
	 * Test Metadata with copyright.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_copyright(): void
	{
		// Arrange
		$metadata = new Metadata();
		$copyright = new Copyright('John Doe', '2024', 'https://creativecommons.org/licenses/by/4.0/');
		
		// Act
		$metadata->copyright = $copyright;
		
		// Assert
		$this->assertInstanceOf(Copyright::class, $metadata->copyright);
		$this->assertEquals('John Doe', $metadata->copyright->author);
		$this->assertEquals('2024', $metadata->copyright->year);
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $metadata->copyright->license);
	}

	/**
	 * Test Metadata with single link.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_single_link(): void
	{
		// Arrange
		$metadata = new Metadata();
		$link = new Link('https://example.com', 'Example Website');
		$link->type = 'text/html';
		
		// Act
		$metadata->links = [$link];
		
		// Assert
		$this->assertCount(1, $metadata->links);
		$this->assertInstanceOf(Link::class, $metadata->links[0]);
		$this->assertEquals('https://example.com', $metadata->links[0]->href);
		$this->assertEquals('Example Website', $metadata->links[0]->text);
		$this->assertEquals('text/html', $metadata->links[0]->type);
	}

	/**
	 * Test Metadata with multiple links.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_multiple_links(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		$link1 = new Link('https://example.com', 'Example Website');
		
		$link2 = new Link('https://photos.example.com/track.jpg', 'Track Photo');
		$link2->type = 'image/jpeg';
		
		// Act
		$metadata->links = [$link1, $link2];
		
		// Assert
		$this->assertCount(2, $metadata->links);
		$this->assertEquals('https://example.com', $metadata->links[0]->href);
		$this->assertEquals('https://photos.example.com/track.jpg', $metadata->links[1]->href);
	}

	/**
	 * Test Metadata with time.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_time(): void
	{
		// Arrange
		$metadata = new Metadata();
		$time = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
		
		// Act
		$metadata->time = $time;
		
		// Assert
		$this->assertInstanceOf(\DateTime::class, $metadata->time);
		$this->assertEquals($time->getTimestamp(), $metadata->time->getTimestamp());
	}

	/**
	 * Test Metadata with keywords.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_keywords(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$metadata->keywords = 'hiking, trail, mountains, nature';
		
		// Assert
		$this->assertEquals('hiking, trail, mountains, nature', $metadata->keywords);
	}

	/**
	 * Test Metadata with bounds.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_bounds(): void
	{
		// Arrange
		$metadata = new Metadata();
		$bounds = new Bounds(50.0, 10.0, 55.0, 15.0);
		
		// Act
		$metadata->bounds = $bounds;
		
		// Assert
		$this->assertInstanceOf(Bounds::class, $metadata->bounds);
		$this->assertEquals(50.0, $metadata->bounds->minLatitude);
		$this->assertEquals(10.0, $metadata->bounds->minLongitude);
		$this->assertEquals(55.0, $metadata->bounds->maxLatitude);
		$this->assertEquals(15.0, $metadata->bounds->maxLongitude);
	}

	/**
	 * Test Metadata with extensions.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_extensions(): void
	{
		// Arrange
		$metadata = new Metadata();
		$extensions = new Extensions();
		
		// Act
		$metadata->extensions = $extensions;
		
		// Assert
		$this->assertInstanceOf(Extensions::class, $metadata->extensions);
	}

	/**
	 * Test Metadata with all fields populated.
	 * Requirements: 2.2
	 */
	public function test_metadata_stores_all_fields(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		$author = new Person();
		$author->name = 'Jane Smith';
		
		$copyright = new Copyright('Jane Smith', '2024');
		
		$link = new Link('https://example.com');
		
		$bounds = new Bounds(50.0, 10.0, 55.0, 15.0);
		$extensions = new Extensions();
		$time = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
		
		// Act
		$metadata->name = 'Complete Track';
		$metadata->description = 'A track with all metadata';
		$metadata->author = $author;
		$metadata->copyright = $copyright;
		$metadata->links = [$link];
		$metadata->time = $time;
		$metadata->keywords = 'complete, test';
		$metadata->bounds = $bounds;
		$metadata->extensions = $extensions;
		
		// Assert
		$this->assertEquals('Complete Track', $metadata->name);
		$this->assertEquals('A track with all metadata', $metadata->description);
		$this->assertInstanceOf(Person::class, $metadata->author);
		$this->assertInstanceOf(Copyright::class, $metadata->copyright);
		$this->assertCount(1, $metadata->links);
		$this->assertInstanceOf(\DateTime::class, $metadata->time);
		$this->assertEquals('complete, test', $metadata->keywords);
		$this->assertInstanceOf(Bounds::class, $metadata->bounds);
		$this->assertInstanceOf(Extensions::class, $metadata->extensions);
	}

	/**
	 * Test Metadata serialization to array with minimal data.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_to_array_with_minimal_data(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertArrayHasKey('name', $array);
		$this->assertArrayHasKey('desc', $array);
		$this->assertArrayHasKey('author', $array);
		$this->assertArrayHasKey('copyright', $array);
		$this->assertArrayHasKey('links', $array);
		$this->assertArrayHasKey('time', $array);
		$this->assertArrayHasKey('keywords', $array);
		$this->assertArrayHasKey('bounds', $array);
		$this->assertArrayHasKey('extensions', $array);
		
		$this->assertNull($array['name']);
		$this->assertNull($array['desc']);
		$this->assertNull($array['author']);
		$this->assertNull($array['copyright']);
		$this->assertNull($array['time']);
		$this->assertNull($array['keywords']);
		$this->assertNull($array['bounds']);
		$this->assertNull($array['extensions']);
	}

	/**
	 * Test Metadata serialization to array with name and description.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_name_and_description_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$metadata->name = 'Test Track';
		$metadata->description = 'Test Description';
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertEquals('Test Track', $array['name']);
		$this->assertEquals('Test Description', $array['desc']);
	}

	/**
	 * Test Metadata serialization to array with author.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_author_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$author = new Person();
		$author->name = 'John Doe';
		$metadata->author = $author;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array['author']);
		$this->assertEquals('John Doe', $array['author']['name']);
	}

	/**
	 * Test Metadata serialization to array with copyright.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_copyright_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$copyright = new Copyright('Jane Smith', '2024', 'https://example.com/license');
		$metadata->copyright = $copyright;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array['copyright']);
		$this->assertEquals('Jane Smith', $array['copyright']['author']);
		$this->assertEquals('2024', $array['copyright']['year']);
		$this->assertEquals('https://example.com/license', $array['copyright']['license']);
	}

	/**
	 * Test Metadata serialization to array with links.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_links_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		$link1 = new Link('https://example.com', 'Example');
		
		$link2 = new Link('https://test.com', 'Test');
		
		$metadata->links = [$link1, $link2];
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array['links']);
		$this->assertCount(2, $array['links']);
		$this->assertEquals('https://example.com', $array['links'][0]['href']);
		$this->assertEquals('Example', $array['links'][0]['text']);
		$this->assertEquals('https://test.com', $array['links'][1]['href']);
		$this->assertEquals('Test', $array['links'][1]['text']);
	}

	/**
	 * Test Metadata serialization to array with time.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_time_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$time = new \DateTime('2024-01-15T10:30:00Z');
		$metadata->time = $time;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertNotNull($array['time']);
		$this->assertIsString($array['time']);
		$this->assertStringContainsString('2024-01-15', $array['time']);
	}

	/**
	 * Test Metadata serialization to array with keywords.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_keywords_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$metadata->keywords = 'hiking, trail, mountains';
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertEquals('hiking, trail, mountains', $array['keywords']);
	}

	/**
	 * Test Metadata serialization to array with bounds.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_bounds_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$bounds = new Bounds(50.0, 10.0, 55.0, 15.0);
		$metadata->bounds = $bounds;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array['bounds']);
		$this->assertEquals(50.0, $array['bounds']['minlat']);
		$this->assertEquals(10.0, $array['bounds']['minlon']);
		$this->assertEquals(55.0, $array['bounds']['maxlat']);
		$this->assertEquals(15.0, $array['bounds']['maxlon']);
	}

	/**
	 * Test Metadata serialization to array with extensions.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_extensions_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$extensions = new Extensions();
		$metadata->extensions = $extensions;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($array['extensions']);
	}

	/**
	 * Test Metadata serialization to array with all fields.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_all_fields_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		$author = new Person();
		$author->name = 'Complete Author';
		
		$copyright = new Copyright('Complete Author', '2024');
		
		$link = new Link('https://complete.example.com');
		
		$bounds = new Bounds(50.0, 10.0, 55.0, 15.0);
		$extensions = new Extensions();
		$time = new \DateTime('2024-01-15T10:30:00Z');
		
		$metadata->name = 'Complete Metadata';
		$metadata->description = 'All fields populated';
		$metadata->author = $author;
		$metadata->copyright = $copyright;
		$metadata->links = [$link];
		$metadata->time = $time;
		$metadata->keywords = 'complete, all, fields';
		$metadata->bounds = $bounds;
		$metadata->extensions = $extensions;
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertEquals('Complete Metadata', $array['name']);
		$this->assertEquals('All fields populated', $array['desc']);
		$this->assertIsArray($array['author']);
		$this->assertEquals('Complete Author', $array['author']['name']);
		$this->assertIsArray($array['copyright']);
		$this->assertEquals('Complete Author', $array['copyright']['author']);
		$this->assertIsArray($array['links']);
		$this->assertCount(1, $array['links']);
		$this->assertNotNull($array['time']);
		$this->assertEquals('complete, all, fields', $array['keywords']);
		$this->assertIsArray($array['bounds']);
		$this->assertIsArray($array['extensions']);
	}

	/**
	 * Test Metadata with empty string values.
	 * Requirements: 2.2
	 */
	public function test_metadata_handles_empty_strings(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$metadata->name = '';
		$metadata->description = '';
		$metadata->keywords = '';
		
		// Assert
		$this->assertEquals('', $metadata->name);
		$this->assertEquals('', $metadata->description);
		$this->assertEquals('', $metadata->keywords);
	}

	/**
	 * Test Metadata serialization with empty strings.
	 * Requirements: 2.7
	 */
	public function test_metadata_serializes_empty_strings_to_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		$metadata->name = '';
		$metadata->description = '';
		$metadata->keywords = '';
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertEquals('', $array['name']);
		$this->assertEquals('', $array['desc']);
		$this->assertEquals('', $array['keywords']);
	}

	/**
	 * Test Metadata with very long strings.
	 * Requirements: 2.2
	 */
	public function test_metadata_handles_long_strings(): void
	{
		// Arrange
		$metadata = new Metadata();
		$longString = str_repeat('A', 1000);
		
		// Act
		$metadata->name = $longString;
		$metadata->description = $longString;
		$metadata->keywords = $longString;
		
		// Assert
		$this->assertEquals($longString, $metadata->name);
		$this->assertEquals($longString, $metadata->description);
		$this->assertEquals($longString, $metadata->keywords);
		$this->assertEquals(1000, strlen($metadata->name));
	}

	/**
	 * Test Metadata with special characters.
	 * Requirements: 2.2
	 */
	public function test_metadata_handles_special_characters(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$metadata->name = 'Track with "quotes" & <special> characters';
		$metadata->description = 'Description with émojis 🏔️ and ümlauts';
		$metadata->keywords = 'special, characters, <>&"\'';
		
		// Assert
		$this->assertEquals('Track with "quotes" & <special> characters', $metadata->name);
		$this->assertEquals('Description with émojis 🏔️ and ümlauts', $metadata->description);
		$this->assertEquals('special, characters, <>&"\'', $metadata->keywords);
	}

	/**
	 * Test Metadata with Unicode characters.
	 * Requirements: 2.2
	 */
	public function test_metadata_handles_unicode_characters(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$metadata->name = '日本語のトラック';
		$metadata->description = 'Описание на русском';
		$metadata->keywords = 'العربية, 中文, हिन्दी';
		
		// Assert
		$this->assertEquals('日本語のトラック', $metadata->name);
		$this->assertEquals('Описание на русском', $metadata->description);
		$this->assertEquals('العربية, 中文, हिन्दी', $metadata->keywords);
	}

	/**
	 * Test Metadata with empty links array.
	 * Requirements: 2.2, 2.7
	 */
	public function test_metadata_with_empty_links_array(): void
	{
		// Arrange
		$metadata = new Metadata();
		
		// Act
		$array = $metadata->toArray();
		
		// Assert
		$this->assertIsArray($metadata->links);
		$this->assertEmpty($metadata->links);
		$this->assertIsArray($array['links']);
	}

	/**
	 * Test Metadata time with different timezones.
	 * Requirements: 2.2
	 */
	public function test_metadata_time_with_different_timezones(): void
	{
		// Arrange
		$metadata = new Metadata();
		$timeUTC = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('UTC'));
		$timeEST = new \DateTime('2024-01-15 10:30:00', new \DateTimeZone('America/New_York'));
		
		// Act
		$metadata->time = $timeUTC;
		$utcTimestamp = $metadata->time->getTimestamp();
		
		$metadata->time = $timeEST;
		$estTimestamp = $metadata->time->getTimestamp();
		
		// Assert
		$this->assertNotEquals($utcTimestamp, $estTimestamp);
		$this->assertInstanceOf(\DateTime::class, $metadata->time);
	}
}
