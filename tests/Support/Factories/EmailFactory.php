<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Email;

/**
 * Factory for creating Email instances for testing.
 */
class EmailFactory
{
	/**
	 * Create an Email with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Email
	 */
	public static function create(array $overrides = []): Email
	{
		$id = $overrides['id'] ?? 'test';
		$domain = $overrides['domain'] ?? 'example.com';

		return new Email($id, $domain);
	}

	/**
	 * Create an Email with a specific address.
	 *
	 * @param string $id The email ID (before @)
	 * @param string $domain The email domain (after @)
	 * @return Email
	 */
	public static function createWithAddress(string $id, string $domain): Email
	{
		return new Email($id, $domain);
	}
}
