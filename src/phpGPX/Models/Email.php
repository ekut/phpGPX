<?php

declare(strict_types=1);

/**
 * Created            16/02/2017 22:59
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use Override;
use phpGPX\Helpers\GpxValidator;

/**
 * Class Email
 * An email address. Broken into two parts (id and domain) to help prevent email harvesting.
 * @see https://www.topografix.com/GPX/1/1/#type_emailType
 * @package phpGPX\Models
 */
final class Email implements Summarizable
{
	/**
	 * Id half of email address (jakub.dubec)
	 * Domain half of email address (gmail.com)
	 */
	public function __construct(
		public string $id,
		public string $domain,
	) {
		GpxValidator::validateNonEmptyString($id, 'Email id attribute');
		GpxValidator::validateNonEmptyString($domain, 'Email domain attribute');
	}

	/**
	 * Serialize object to array
	 * @return array{id: string, domain: string}
	 */
	#[Override]
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'domain' => $this->domain,
		];
	}
}
