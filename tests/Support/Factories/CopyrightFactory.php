<?php

declare(strict_types=1);

namespace phpGPX\Tests\Support\Factories;

use phpGPX\Models\Copyright;

/**
 * Factory for creating Copyright instances for testing.
 */
class CopyrightFactory
{
	/**
	 * Create a Copyright with default or custom values.
	 *
	 * @param array $overrides Array of property values to override defaults
	 * @return Copyright
	 */
	public static function create(array $overrides = []): Copyright
	{
		$author = $overrides['author'] ?? 'Test Author';
		$year = $overrides['year'] ?? null;
		$license = $overrides['license'] ?? null;

		return new Copyright($author, $year, $license);
	}

	/**
	 * Create a Copyright with year.
	 *
	 * @param string $year The copyright year
	 * @return Copyright
	 */
	public static function createWithYear(string $year): Copyright
	{
		return self::create(['year' => $year]);
	}

	/**
	 * Create a Copyright with license.
	 *
	 * @param string $license The license URL
	 * @return Copyright
	 */
	public static function createWithLicense(string $license): Copyright
	{
		return self::create(['license' => $license]);
	}
}
