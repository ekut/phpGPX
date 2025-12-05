<?php
/**
 * Created            16/02/2017 23:08
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use phpGPX\Models\Person;

/**
 * Class PersonParser
 * @package phpGPX\Parsers
 */
abstract class PersonParser
{
	public static $tagName = 'author';

	/**
	 * @return Person
	 */
	public static function parse(\SimpleXMLElement $node)
	{
		$name = property_exists($node, 'name') && $node->name !== null ? ((string) $node->name) : null;
		$email = property_exists($node, 'email') && $node->email !== null ? EmailParser::parse($node->email) : null;
		$links = property_exists($node, 'link') && $node->link !== null ? LinkParser::parse($node->link) : [];

		return new Person($name, $email, $links);
	}

	public static function toXML(Person $person, \DOMDocument &$document)
	{
		$node =  $document->createElement(self::$tagName);

		if (!empty($person->name)) {
			$child = $document->createElement('name', $person->name);
			$node->appendChild($child);
		}

		if (!empty($person->email)) {
			$child = EmailParser::toXML($person->email, $document);
			$node->appendChild($child);
		}

		# TODO: is_iterable
		if (!is_null($person->links)) {
			foreach ($person->links as $link) {
				$child = LinkParser::toXML($link, $document);
				$node->appendChild($child);
			}
		}

		return $node;
	}
}
