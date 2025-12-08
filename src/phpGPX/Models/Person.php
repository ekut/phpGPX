<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 22:58
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\SerializationHelper;

/**
 * Class Person
 * A person or organisation
 * @see https://www.topografix.com/GPX/1/1/#type_personType
 * @package phpGPX\Models
 */
final class Person implements Summarizable
{
	/**
	 * Name of person or organization.
	 * An original GPX 1.1 attribute.
	 * E-mail address.
	 * An original GPX 1.1 attribute.
	 * Link to Web site or other external information about person.
	 * An original GPX 1.1 attribute.
	 * @param Link[] $links
	 */
	public function __construct(
		public ?string $name = null,
		public ?Email $email = null,
		public array $links = [],
	) {
	}

	/**
	 * Serialize object to array
	 * @return array{name: string|null, email: array<int|string, mixed>|null, links: array<int|string, mixed>|null}
	 */
	#[\Override]
	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'email' => SerializationHelper::serialize($this->email),
			'links' => SerializationHelper::serialize($this->links),
		];
	}
}
