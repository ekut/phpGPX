<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Link;

/**
 * Factory for creating Link instances for testing.
 */
class LinkFactory
{
	/**
	 * Create a Link with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Link
	 */
	public static function create(array $overrides = []): Link
	{
		$href = $overrides['href'] ?? 'https://example.com';
		$text = $overrides['text'] ?? null;
		$type = $overrides['type'] ?? null;

		return new Link($href, $text, $type);
	}

	/**
	 * Create a Link with text.
	 *
	 * @param string $text The link text
	 * @return Link
	 */
	public static function createWithText(string $text): Link
	{
		return self::create(['text' => $text]);
	}

	/**
	 * Create a Link with a specific URL.
	 *
	 * @param string $href The URL
	 * @return Link
	 */
	public static function createWithUrl(string $href): Link
	{
		return self::create(['href' => $href]);
	}
}
