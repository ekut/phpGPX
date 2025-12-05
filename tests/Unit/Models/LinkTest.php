<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use phpGPX\Models\Link;
use phpGPX\Parsers\LinkParser;
use phpGPX\Tests\Support\TestCase;

/**
 * Unit tests for Link model.
 * Tests Link creation, property storage, and serialization.
 */
final class LinkTest extends TestCase
{
	/**
	 * Test Link creation with required href.
	 * Requirements: 2.2
	 */
	public function test_link_can_be_created_with_href(): void
	{
		// Arrange & Act
		$link = new Link();
		$link->href = 'https://example.com';
		
		// Assert
		$this->assertInstanceOf(Link::class, $link);
		$this->assertEquals('https://example.com', $link->href);
	}

	/**
	 * Test Link creation with all properties.
	 * Requirements: 2.2
	 */
	public function test_link_stores_all_properties_correctly(): void
	{
		// Arrange & Act
		$link = new Link();
		$link->href = 'https://example.com/photo.jpg';
		$link->text = 'Example Photo';
		$link->type = 'image/jpeg';
		
		// Assert
		$this->assertEquals('https://example.com/photo.jpg', $link->href);
		$this->assertEquals('Example Photo', $link->text);
		$this->assertEquals('image/jpeg', $link->type);
	}

	/**
	 * Test Link with optional fields as null.
	 * Requirements: 2.2
	 */
	public function test_link_handles_optional_fields_as_null(): void
	{
		// Arrange & Act
		$link = new Link();
		$link->href = 'https://example.com';
		
		// Assert
		$this->assertNull($link->text);
		$this->assertNull($link->type);
	}

	/**
	 * Test Link initialization has null values.
	 * Requirements: 2.2
	 */
	public function test_link_initialization_has_null_values(): void
	{
		// Arrange & Act
		$link = new Link();
		
		// Assert - with constructor promotion, href has empty string default
		$this->assertSame('', $link->href);
		$this->assertNull($link->text);
		$this->assertNull($link->type);
	}

	/**
	 * Test Link serialization to array.
	 * Requirements: 2.7
	 */
	public function test_link_serializes_to_array_correctly(): void
	{
		// Arrange
		$link = new Link();
		$link->href = 'https://example.com';
		$link->text = 'Example Link';
		$link->type = 'text/html';
		
		// Act
		$array = $link->toArray();
		
		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('https://example.com', $array['href']);
		$this->assertEquals('Example Link', $array['text']);
		$this->assertEquals('text/html', $array['type']);
	}

	/**
	 * Test Link serialization to array with null values.
	 * Requirements: 2.7
	 */
	public function test_link_serializes_null_values_correctly(): void
	{
		// Arrange
		$link = new Link();
		$link->href = 'https://example.com';
		
		// Act
		$array = $link->toArray();
		
		// Assert
		$this->assertNull($array['text']);
		$this->assertNull($array['type']);
	}

	/**
	 * Test Link serialization to XML.
	 * Requirements: 2.5
	 */
	public function test_link_serializes_to_xml_correctly(): void
	{
		// Arrange
		$link = new Link();
		$link->href = 'https://example.com/photo.jpg';
		$link->text = 'Example Photo';
		$link->type = 'image/jpeg';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = LinkParser::toXML($link, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<link', $xml);
		$this->assertStringContainsString('href="https://example.com/photo.jpg"', $xml);
		$this->assertStringContainsString('<text>Example Photo</text>', $xml);
		$this->assertStringContainsString('<type>image/jpeg</type>', $xml);
	}

	/**
	 * Test Link serialization to XML with only href.
	 * Requirements: 2.5
	 */
	public function test_link_serializes_to_xml_with_only_href(): void
	{
		// Arrange
		$link = new Link();
		$link->href = 'https://example.com';
		
		$document = new \DOMDocument('1.0', 'UTF-8');
		
		// Act
		$xmlElement = LinkParser::toXML($link, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();
		
		// Assert
		$this->assertStringContainsString('<link', $xml);
		$this->assertStringContainsString('href="https://example.com"', $xml);
		$this->assertStringNotContainsString('<text>', $xml);
		$this->assertStringNotContainsString('<type>', $xml);
	}

	/**
	 * Test Link with various MIME types.
	 * Requirements: 2.2
	 */
	public function test_link_handles_various_mime_types(): void
	{
		// Arrange & Act - Image
		$imageLink = new Link();
		$imageLink->href = 'https://example.com/photo.jpg';
		$imageLink->type = 'image/jpeg';
		
		// Assert
		$this->assertEquals('image/jpeg', $imageLink->type);
		
		// Arrange & Act - Video
		$videoLink = new Link();
		$videoLink->href = 'https://example.com/video.mp4';
		$videoLink->type = 'video/mp4';
		
		// Assert
		$this->assertEquals('video/mp4', $videoLink->type);
		
		// Arrange & Act - HTML
		$htmlLink = new Link();
		$htmlLink->href = 'https://example.com/page.html';
		$htmlLink->type = 'text/html';
		
		// Assert
		$this->assertEquals('text/html', $htmlLink->type);
	}

	/**
	 * Test Link with different URL schemes.
	 * Requirements: 2.2
	 */
	public function test_link_handles_different_url_schemes(): void
	{
		// Arrange & Act - HTTPS
		$httpsLink = new Link();
		$httpsLink->href = 'https://example.com';
		$this->assertEquals('https://example.com', $httpsLink->href);
		
		// Arrange & Act - HTTP
		$httpLink = new Link();
		$httpLink->href = 'http://example.com';
		$this->assertEquals('http://example.com', $httpLink->href);
		
		// Arrange & Act - FTP
		$ftpLink = new Link();
		$ftpLink->href = 'ftp://example.com/file.gpx';
		$this->assertEquals('ftp://example.com/file.gpx', $ftpLink->href);
	}

	/**
	 * Test Link with special characters in text.
	 * Requirements: 2.2
	 */
	public function test_link_handles_special_characters_in_text(): void
	{
		// Arrange & Act
		$link = new Link();
		$link->href = 'https://example.com';
		$link->text = 'Photo & Video <Collection>';
		
		// Assert
		$this->assertEquals('Photo & Video <Collection>', $link->text);
	}
}
