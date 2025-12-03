<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Metadata;
use phpGPX\Models\Person;
use phpGPX\Models\Link;

/**
 * Factory for creating Metadata instances for testing.
 */
class MetadataFactory
{
	/**
	 * Create Metadata with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Metadata
	 */
	public static function create(array $overrides = []): Metadata
	{
		$metadata = new Metadata();
		$metadata->name = $overrides['name'] ?? 'Test GPX File';
		$metadata->description = $overrides['description'] ?? 'Test description';
		$metadata->time = $overrides['time'] ?? new \DateTime();
		
		if (isset($overrides['author'])) {
			$metadata->author = $overrides['author'];
		}
		
		if (isset($overrides['links'])) {
			$metadata->links = $overrides['links'];
		}
		
		return $metadata;
	}

	/**
	 * Create Metadata with author information.
	 *
	 * @param string $authorName The author's name
	 * @return Metadata
	 */
	public static function createWithAuthor(string $authorName): Metadata
	{
		$author = new Person();
		$author->name = $authorName;
		
		return self::create(['author' => $author]);
	}

	/**
	 * Create Metadata with links.
	 *
	 * @param array $linkUrls Array of link URLs
	 * @return Metadata
	 */
	public static function createWithLinks(array $linkUrls): Metadata
	{
		$links = [];
		foreach ($linkUrls as $url) {
			$link = new Link();
			$link->href = $url;
			$links[] = $link;
		}
		
		return self::create(['links' => $links]);
	}
}
