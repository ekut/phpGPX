<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Email;
use phpGPX\Models\Link;
use phpGPX\Models\Person;
use phpGPX\Parsers\PersonParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Person model.
 * Tests Person creation, property storage, and serialization.
 */
final class PersonTest extends TestCase
{
	/**
	 * Test Person creation with name.
	 * Requirements: 2.2
	 */
	public function test_person_can_be_created_with_name(): void
	{
		// Arrange & Act
		$person = new Person();
		$person->name = 'John Doe';
		
		// Assert
		$this->assertInstanceOf(Person::class, $person);
		$this->assertEquals('John Doe', $person->name);
	}

	/**
	 * Test Person stores all properties correctly.
	 * Requirements: 2.2
	 */
	public function test_person_stores_all_properties_correctly(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		$link1 = new Link();
		$link1->href = 'https://example.com';
		$link1->text = 'Website';
		
		$link2 = new Link();
		$link2->href = 'https://example.com/photo.jpg';
		$link2->text = 'Photo';
		$link2->type = 'image/jpeg';
		
		// Act
		$person = new Person();
		$person->name = 'John Doe';
		$person->email = $email;
		$person->links = [$link1, $link2];
		
		// Assert
		$this->assertEquals('John Doe', $person->name);
		$this->assertInstanceOf(Email::class, $person->email);
		$this->assertEquals('john.doe', $person->email->id);
		$this->assertEquals('example.com', $person->email->domain);
		$this->assertIsArray($person->links);
		$this->assertCount(2, $person->links);
		$this->assertInstanceOf(Link::class, $person->links[0]);
		$this->assertInstanceOf(Link::class, $person->links[1]);
	}

	/**
	 * Test Person with optional fields as null.
	 * Requirements: 2.2
	 */
	public function test_person_handles_optional_fields_as_null(): void
	{
		// Arrange & Act
		$person = new Person();
		$person->name = 'Jane Smith';
		
		// Assert
		$this->assertNull($person->email);
		// With constructor promotion, links has empty array default
		$this->assertSame([], $person->links);
	}

	/**
	 * Test Person initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_person_initialization_has_null_values(): void
	{
		// Arrange & Act
		$person = new Person();
		
		// Assert
		$this->assertNull($person->name);
		$this->assertNull($person->email);
		// With constructor promotion, links has empty array default
		$this->assertSame([], $person->links);
	}

	/**
	 * Test Person serialization to array.
	 * Requirements: 2.7
	 */
	public function test_person_serializes_to_array_correctly(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		$link = new Link();
		$link->href = 'https://example.com';
		$link->text = 'Website';
		
		$person = new Person();
		$person->name = 'John Doe';
		$person->email = $email;
		$person->links = [$link];
		
		// Act
		$array = $person->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('John Doe', $array['name']);
		$this->assertIsArray($array['email']);
		$this->assertEquals('john.doe', $array['email']['id']);
		$this->assertEquals('example.com', $array['email']['domain']);
		$this->assertIsArray($array['links']);
		$this->assertCount(1, $array['links']);
	}

	/**
	 * Test Person serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_person_serializes_null_values_correctly(): void
	{
		// Arrange
		$person = new Person();
		$person->name = 'John Doe';
		
		// Act
		$array = $person->toArray();
		
		// Assert
		$this->assertNull($array['email']);
		// With constructor promotion, links has empty array default which serializes to empty array
		$this->assertSame([], $array['links']);
	}

	/**
	 * Test Person serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_person_serializes_to_xml_correctly(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		$link = new Link();
		$link->href = 'https://example.com';
		$link->text = 'Website';
		
		$person = new Person();
		$person->name = 'John Doe';
		$person->email = $email;
		$person->links = [$link];
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PersonParser::toXML($person, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<author>', $xml);
		$this->assertStringContainsString('<name>John Doe</name>', $xml);
		$this->assertStringContainsString('<email', $xml);
		$this->assertStringContainsString('id="john.doe"', $xml);
		$this->assertStringContainsString('domain="example.com"', $xml);
		$this->assertStringContainsString('<link', $xml);
		$this->assertStringContainsString('href="https://example.com"', $xml);
	}

	/**
	 * Test Person serialization to XML with only name.
	 * Requirements: 2.5
	 */
	public function test_person_serializes_to_xml_with_only_name(): void
	{
		// Arrange
		$person = new Person();
		$person->name = 'John Doe';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PersonParser::toXML($person, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<author>', $xml);
		$this->assertStringContainsString('<name>John Doe</name>', $xml);
		$this->assertStringNotContainsString('<email', $xml);
		$this->assertStringNotContainsString('<link', $xml);
	}

	/**
	 * Test Person with multiple links.
	 * Requirements: 2.2
	 */
	public function test_person_handles_multiple_links(): void
	{
		// Arrange
		$link1 = new Link();
		$link1->href = 'https://example.com';
		$link1->text = 'Website';
		
		$link2 = new Link();
		$link2->href = 'https://example.com/photo.jpg';
		$link2->text = 'Photo';
		
		$link3 = new Link();
		$link3->href = 'https://example.com/video.mp4';
		$link3->text = 'Video';
		
		// Act
		$person = new Person();
		$person->name = 'John Doe';
		$person->links = [$link1, $link2, $link3];
		
		// Assert
		$this->assertCount(3, $person->links);
		$this->assertEquals('https://example.com', $person->links[0]->href);
		$this->assertEquals('https://example.com/photo.jpg', $person->links[1]->href);
		$this->assertEquals('https://example.com/video.mp4', $person->links[2]->href);
	}

	/**
	 * Test Person serialization to XML with multiple links.
	 * Requirements: 2.5
	 */
	public function test_person_serializes_multiple_links_to_xml(): void
	{
		// Arrange
		$link1 = new Link();
		$link1->href = 'https://example.com';
		
		$link2 = new Link();
		$link2->href = 'https://example.com/photo.jpg';
		
		$person = new Person();
		$person->name = 'John Doe';
		$person->links = [$link1, $link2];
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = PersonParser::toXML($person, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('href="https://example.com"', $xml);
		$this->assertStringContainsString('href="https://example.com/photo.jpg"', $xml);
		// Count occurrences of <link
		$this->assertEquals(2, substr_count($xml, '<link'));
	}

	/**
	 * Test Person with organization name.
	 * Requirements: 2.2
	 */
	public function test_person_can_represent_organization(): void
	{
		// Arrange & Act
		$person = new Person();
		$person->name = 'Acme Corporation';
		
		$email = new Email();
		$email->id = 'info';
		$email->domain = 'acme.com';
		$person->email = $email;
		
		// Assert
		$this->assertEquals('Acme Corporation', $person->name);
		$this->assertEquals('info', $person->email->id);
		$this->assertEquals('acme.com', $person->email->domain);
	}

	/**
	 * Test Person with special characters in name.
	 * Requirements: 2.2
	 */
	public function test_person_handles_special_characters_in_name(): void
	{
		// Arrange & Act
		$person = new Person();
		$person->name = 'José García-López';
		
		// Assert
		$this->assertEquals('José García-López', $person->name);
	}

	/**
	 * Test Person with empty links array.
	 * Requirements: 2.2
	 */
	public function test_person_handles_empty_links_array(): void
	{
		// Arrange & Act
		$person = new Person();
		$person->name = 'John Doe';
		$person->links = [];
		
		// Assert
		$this->assertIsArray($person->links);
		$this->assertEmpty($person->links);
	}
}
