<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Email;
use phpGPX\Parsers\EmailParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Email model.
 * Tests Email creation, property storage, and serialization.
 */
final class EmailTest extends TestCase
{
	/**
	 * Test Email creation with id and domain.
	 * Requirements: 2.2
	 */
	public function test_email_can_be_created_with_id_and_domain(): void
	{
		// Arrange & Act
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		// Assert
		$this->assertInstanceOf(Email::class, $email);
		$this->assertEquals('john.doe', $email->id);
		$this->assertEquals('example.com', $email->domain);
	}

	/**
	 * Test Email stores both parts correctly.
	 * Requirements: 2.2
	 */
	public function test_email_stores_both_parts_correctly(): void
	{
		// Arrange & Act
		$email = new Email();
		$email->id = 'test.user';
		$email->domain = 'mail.example.org';
		
		// Assert
		$this->assertEquals('test.user', $email->id);
		$this->assertEquals('mail.example.org', $email->domain);
	}

	/**
	 * Test Email initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_email_initialization_has_null_values(): void
	{
		// Arrange & Act
		$email = new Email();
		
		// Assert - with constructor promotion, defaults are empty strings
		$this->assertSame('', $email->id);
		$this->assertSame('', $email->domain);
	}

	/**
	 * Test Email serialization to array.
	 * Requirements: 2.7
	 */
	public function test_email_serializes_to_array_correctly(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		// Act
		$array = $email->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('john.doe', $array['id']);
		$this->assertEquals('example.com', $array['domain']);
	}

	/**
	 * Test Email serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_email_serializes_null_values_as_empty_strings(): void
	{
		// Arrange
		$email = new Email();
		
		// Act
		$array = $email->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('', $array['id']);
		$this->assertEquals('', $array['domain']);
	}

	/**
	 * Test Email serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_email_serializes_to_xml_correctly(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'john.doe';
		$email->domain = 'example.com';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = EmailParser::toXML($email, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<email', $xml);
		$this->assertStringContainsString('id="john.doe"', $xml);
		$this->assertStringContainsString('domain="example.com"', $xml);
	}

	/**
	 * Test Email serialization to XML with empty values.
	 * Requirements: 2.5
	 */
	public function test_email_serializes_to_xml_without_empty_attributes(): void
	{
		// Arrange
		$email = new Email();
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = EmailParser::toXML($email, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<email', $xml);
		$this->assertStringNotContainsString('id=', $xml);
		$this->assertStringNotContainsString('domain=', $xml);
	}

	/**
	 * Test Email with various id formats.
	 * Requirements: 2.2
	 */
	public function test_email_handles_various_id_formats(): void
	{
		// Arrange & Act - Simple id
		$email1 = new Email();
		$email1->id = 'user';
		$email1->domain = 'example.com';
		$this->assertEquals('user', $email1->id);
		
		// Arrange & Act - Id with dot
		$email2 = new Email();
		$email2->id = 'first.last';
		$email2->domain = 'example.com';
		$this->assertEquals('first.last', $email2->id);
		
		// Arrange & Act - Id with plus
		$email3 = new Email();
		$email3->id = 'user+tag';
		$email3->domain = 'example.com';
		$this->assertEquals('user+tag', $email3->id);
		
		// Arrange & Act - Id with underscore
		$email4 = new Email();
		$email4->id = 'user_name';
		$email4->domain = 'example.com';
		$this->assertEquals('user_name', $email4->id);
	}

	/**
	 * Test Email with various domain formats.
	 * Requirements: 2.2
	 */
	public function test_email_handles_various_domain_formats(): void
	{
		// Arrange & Act - Simple domain
		$email1 = new Email();
		$email1->id = 'user';
		$email1->domain = 'example.com';
		$this->assertEquals('example.com', $email1->domain);
		
		// Arrange & Act - Subdomain
		$email2 = new Email();
		$email2->id = 'user';
		$email2->domain = 'mail.example.com';
		$this->assertEquals('mail.example.com', $email2->domain);
		
		// Arrange & Act - Country code TLD
		$email3 = new Email();
		$email3->id = 'user';
		$email3->domain = 'example.co.uk';
		$this->assertEquals('example.co.uk', $email3->domain);
		
		// Arrange & Act - New TLD
		$email4 = new Email();
		$email4->id = 'user';
		$email4->domain = 'example.tech';
		$this->assertEquals('example.tech', $email4->domain);
	}

	/**
	 * Test Email represents complete email address.
	 * Requirements: 2.2
	 */
	public function test_email_parts_represent_complete_address(): void
	{
		// Arrange
		$email = new Email();
		$email->id = 'contact';
		$email->domain = 'company.com';
		
		// Act - Reconstruct email address
		$fullEmail = $email->id . '@' . $email->domain;
		
		// Assert
		$this->assertEquals('contact@company.com', $fullEmail);
	}

	/**
	 * Test Email with special characters.
	 * Requirements: 2.2
	 */
	public function test_email_handles_special_characters(): void
	{
		// Arrange & Act
		$email = new Email();
		$email->id = 'user.name+tag';
		$email->domain = 'sub-domain.example.com';
		
		// Assert
		$this->assertEquals('user.name+tag', $email->id);
		$this->assertEquals('sub-domain.example.com', $email->domain);
	}
}
