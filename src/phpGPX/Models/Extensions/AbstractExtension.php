<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 16:14
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models\Extensions;

use phpGPX\Models\Summarizable;

/**
 * @psalm-api
 */
abstract class AbstractExtension implements Summarizable
{
	/**
	 * XML namespace of extension
	 * @api
	 * @psalm-suppress PossiblyUnusedProperty Used by extension implementations and accessed in tests
	 */
	public string $namespace;

	/**
	 * Node name extension.
	 * @api
	 * @psalm-suppress PossiblyUnusedProperty Used by extension implementations and accessed in tests
	 */
	public string $extensionName;

	/**
	 * AbstractExtension constructor.
	 */
	public function __construct(string $namespace, string $extensionName)
	{
		$this->namespace = $namespace;
		$this->extensionName = $extensionName;
	}
}
