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
 * Class PersonParser
 * @package phpGPX\Parsers
 */
abstract class PersonParser
{
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

		if ($person->email !== null) {
			$child = EmailParser::toXML($person->email, $document);
			$node->appendChild($child);
		}

		if (count($person->links) > 0) {
			foreach ($person->links as $link) {
				$child = LinkParser::toXML($link, $document);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
