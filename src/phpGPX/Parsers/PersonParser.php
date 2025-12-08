<?php

declare(strict_types=1);
/**
 * Created            16/02/2017 23:08
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Person;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Person objects.
 *
 * This class provides static methods for converting between XML/SimpleXML
 * and Person model objects. It is not meant to be instantiated.
 *
 * @package phpGPX\Parsers
 */
final class PersonParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 */
	private function __construct()
	{
	}

	public static string $tagName = 'author';

	public static function parse(SimpleXMLElement $node): Person
	{
		$name = property_exists($node, 'name') && $node->name !== null ? ((string) $node->name) : null;
		$email = property_exists($node, 'email') && $node->email !== null ? EmailParser::parse($node->email) : null;
		$links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : [];

		return new Person($name, $email, $links);
	}

	public static function toXML(Person $person, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node =  $document->createElement($tagName);

		if ($person->name !== null && $person->name !== '') {
			$child = $document->createElement('name', $person->name);
			$node->appendChild($child);
		}

		if ($person->email instanceof \phpGPX\Models\Email) {
			$child = EmailParser::toXML($person->email, $document);
			$node->appendChild($child);
		}

		foreach ($person->links as $link) {
			$child = LinkParser::toXML($link, $document);
			$node->appendChild($child);
		}

		return $node;
	}
}
