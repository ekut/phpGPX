<?php

declare(strict_types=1);
/**
 * Created            16/02/2017 22:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Copyright;
use SimpleXMLElement;

/**
 * Class CopyrightParser
 * @package phpGPX\Parsers
 */
abstract class CopyrightParser
{
	public static string $tagName = 'copyright';

	public static function parse(SimpleXMLElement $node): ?Copyright
	{
		if ($node->getName() !== self::$tagName) {
			return null;
		}

		$author = isset($node['author']) ? (string) $node['author'] : '';

		// Author is required by GPX 1.1 spec
		if (trim($author) === '') {
			return null;
		}

		$year = property_exists($node, 'year') && $node->year !== null ? (string) $node->year : null;
		$license = property_exists($node, 'license') && $node->license !== null ? (string) $node->license : null;

		return new Copyright($author, $year, $license);
	}

	public static function toXML(Copyright $copyright, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node = $document->createElement($tagName);

		$node->setAttribute('author', $copyright->author);

		if (!empty($copyright->year)) {
			$child = $document->createElement('year', $copyright->year);
			$node->appendChild($child);
		}

		if (!empty($copyright->license)) {
			$child = $document->createElement('license', $copyright->license);
			$node->appendChild($child);
		}

		return $node;
	}
}
