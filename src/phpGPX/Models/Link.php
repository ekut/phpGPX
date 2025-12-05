<?php

declare(strict_types=1);

/**
 * Created            14/02/2017 18:17
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Models;

use phpGPX\Helpers\GpxValidator;

/**
 * Class Link according to GPX 1.1 specification.
 * A link to an external resource (Web page, digital photo, video clip, etc) with additional information.
 * @see https://www.topografix.com/GPX/1/1/#type_linkType
 * @package phpGPX\Models
 */
class Link implements Summarizable
{
	/**
	 * URL of hyperlink.
	 * Text of hyperlink.
	 * Mime type of content (image/jpeg)
	 */
	public function __construct(
		public string $href,
		public ?string $text = null,
		public ?string $type = null,
	) {
		GpxValidator::validateNonEmptyString($href, 'Link href attribute');
	}

	/**
	 * Serialize object to array
	 */
	public function toArray(): array
	{
		return [
			'href' => $this->href,
			'text' => $this->text,
			'type' => $this->type,
		];
	}
}
