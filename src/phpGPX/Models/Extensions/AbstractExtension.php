<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 16:14
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models\Extensions;

use phpGPX\Models\Summarizable;

abstract class AbstractExtension implements Summarizable
{
	/**
	 * XML namespace of extension
	 */
	public string $namespace;

	/**
	 * Node name extension.
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
