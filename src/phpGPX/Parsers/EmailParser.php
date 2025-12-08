<?php

declare(strict_types=1);
/**
 * Created            16/02/2017 23:02
 * @author            Jakub Dubec <jakub.dubec@gmail.com>
 */

namespace phpGPX\Parsers;

use DOMDocument;
use DOMElement;
use phpGPX\Models\Email;
use SimpleXMLElement;

/**
 * Utility class for parsing and serializing Email objects.
 * 
 * This class provides static methods for converting between XML/SimpleXML
 * and Email model objects. It is not meant to be instantiated.
 * 
 * @package phpGPX\Parsers
 */
final class EmailParser
{
	/**
	 * Private constructor prevents instantiation of this utility class.
	 * 
	 * @psalm-suppress UnusedConstructor
	 */
	private function __construct()
	{
	}

	private static string $tagName = 'email';

	public static function parse(SimpleXMLElement $node): ?Email
	{
		$id = isset($node['id']) ? (string) $node['id'] : '';
		$domain = isset($node['domain']) ? (string) $node['domain'] : '';

		// Both id and domain are required by GPX 1.1 spec
		if (trim($id) === '' || trim($domain) === '') {
			return null;
		}

		return new Email($id, $domain);
	}

	public static function toXML(Email $email, DOMDocument &$document): DOMElement
	{
		$tagName = self::$tagName;
		$node =  $document->createElement($tagName);

		if (!empty($email->id)) {
			$node->setAttribute('id', $email->id);
		}

		if (!empty($email->domain)) {
			$node->setAttribute('domain', $email->domain);
		}

		return $node;
	}
}
