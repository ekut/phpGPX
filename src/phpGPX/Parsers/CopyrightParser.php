<?php
/**
 * Created            16/02/2017 22:45
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use phpGPX\Models\Copyright;

/**
 * Class CopyrightParser
 * @package phpGPX\Parsers
 */
abstract class CopyrightParser
{
	public static $tagName = 'copyright';

	/**
  * @return Copyright|null
  */
 public static function parse(\SimpleXMLElement $node)
	{
		if ($node->getName() != self::$tagName) {
			return null;
		}

		$copyright = new Copyright();

		$copyright->author = isset($node['author']) ? (string) $node['author'] : null;
		$copyright->year = property_exists($node, 'year') && $node->year !== null ? (string) $node->year : null;
		$copyright->license = property_exists($node, 'license') && $node->license !== null ? (string) $node->license : null;

		return $copyright;
	}

	/**
  * @return \DOMElement
  */
 public static function toXML(Copyright $copyright, \DOMDocument &$document)
	{
		$node = $document->createElement(self::$tagName);

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
