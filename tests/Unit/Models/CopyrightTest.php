<?php

declare(strict_types=1);

namespace phpGPX\Tests\Unit\Models;

use DOMDocument;
use Eris\Generators;
use Eris\TestTrait;
use InvalidArgumentException;
use phpGPX\Models\Copyright;
use phpGPX\Parsers\CopyrightParser;
use phpGPX\Tests\Support\TestCase;
use TypeError;

/**
 * Unit tests for Copyright model.
 * Tests Copyright creation, property storage, and serialization.
 */
final class CopyrightTest extends TestCase
{
	use TestTrait;

	/**
	 * Test Copyright creation with author.
	 * Requirements: 4.1
	 */
	public function test_copyright_can_be_created_with_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright('John Doe');

		// Assert
		$this->assertInstanceOf(Copyright::class, $copyright);
		$this->assertEquals('John Doe', $copyright->author);
	}

	/**
	 * Test Copyright construction without author throws TypeError.
	 * Requirements: 4.1
	 */
	public function test_copyright_construction_without_author_throws_type_error(): void
	{
		// Assert
		$this->expectException(TypeError::class);

		// Act
		new Copyright();
	}

	/**
	 * Test Copyright construction with empty author throws InvalidArgumentException.
	 * Requirements: 4.2, 9.4
	 */
	public function test_copyright_construction_with_empty_author_throws_exception(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Copyright author');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new Copyright('');
	}

	/**
	 * Test Copyright construction with whitespace-only author throws InvalidArgumentException.
	 * Requirements: 4.2, 9.4
	 */
	public function test_copyright_construction_with_whitespace_only_author_throws_exception(): void
	{
		// Assert
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Copyright author');
		$this->expectExceptionMessage('required');
		$this->expectExceptionMessage('cannot be empty');

		// Act
		new Copyright('   ');
	}

	/**
	 * Test Copyright validation error message includes field name.
	 * Requirements: 9.4
	 */
	public function test_copyright_validation_error_message_includes_field_name(): void
	{
		// Arrange
		$exceptionThrown = false;
		$exceptionMessage = '';

		// Act
		try {
			new Copyright('');
		} catch (InvalidArgumentException $e) {
			$exceptionThrown = true;
			$exceptionMessage = $e->getMessage();
		}

		// Assert
		$this->assertTrue($exceptionThrown);
		$this->assertStringContainsString('Copyright author', $exceptionMessage);
	}

	/**
	 * Test Copyright stores all properties correctly.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_stores_all_properties_correctly(): void
	{
		// Arrange & Act
		$copyright = new Copyright('John Doe', '2024', 'https://creativecommons.org/licenses/by/4.0/');

		// Assert
		$this->assertEquals('John Doe', $copyright->author);
		$this->assertEquals('2024', $copyright->year);
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $copyright->license);
	}

	/**
	 * Test Copyright with optional fields as null.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_handles_optional_fields_as_null(): void
	{
		// Arrange & Act
		$copyright = new Copyright('John Doe');

		// Assert
		$this->assertEquals('John Doe', $copyright->author);
		$this->assertNull($copyright->year);
		$this->assertNull($copyright->license);
	}

	/**
	 * Test Copyright serialization to array.
	 * Requirements: 4.3
	 */
	public function test_copyright_serializes_to_array_correctly(): void
	{
		// Arrange
		$copyright = new Copyright('John Doe', '2024', 'https://creativecommons.org/licenses/by/4.0/');

		// Act
		$array = $copyright->toArray();

		// Assert
		$this->assertIsArray($array);
		$this->assertEquals('John Doe', $array['author']);
		$this->assertEquals('2024', $array['year']);
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $array['license']);
	}

	/**
	 * Test Copyright serialization to array with null values.
	 * Requirements: 4.3
	 */
	public function test_copyright_serializes_null_values_correctly(): void
	{
		// Arrange
		$copyright = new Copyright('John Doe');

		// Act
		$array = $copyright->toArray();

		// Assert
		$this->assertNull($array['year']);
		$this->assertNull($array['license']);
	}

	/**
	 * Test Copyright serialization to XML.
	 * Requirements: 4.3
	 */
	public function test_copyright_serializes_to_xml_correctly(): void
	{
		// Arrange
		$copyright = new Copyright('John Doe', '2024', 'https://creativecommons.org/licenses/by/4.0/');

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<copyright', $xml);
		$this->assertStringContainsString('author="John Doe"', $xml);
		$this->assertStringContainsString('<year>2024</year>', $xml);
		$this->assertStringContainsString('<license>https://creativecommons.org/licenses/by/4.0/</license>', $xml);
	}

	/**
	 * Test Copyright serialization to XML with only author.
	 * Requirements: 4.3
	 */
	public function test_copyright_serializes_to_xml_with_only_author(): void
	{
		// Arrange
		$copyright = new Copyright('John Doe');

		$document = new DOMDocument('1.0', 'UTF-8');

		// Act
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert
		$this->assertStringContainsString('<copyright', $xml);
		$this->assertStringContainsString('author="John Doe"', $xml);
		$this->assertStringNotContainsString('<year>', $xml);
		$this->assertStringNotContainsString('<license>', $xml);
	}

	/**
	 * Test Copyright with various year formats.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_handles_various_year_formats(): void
	{
		// Arrange & Act - Single year
		$copyright1 = new Copyright('John Doe', '2024');
		$this->assertEquals('2024', $copyright1->year);

		// Arrange & Act - Year range
		$copyright2 = new Copyright('John Doe', '2020-2024');
		$this->assertEquals('2020-2024', $copyright2->year);

		// Arrange & Act - Multiple years
		$copyright3 = new Copyright('John Doe', '2020, 2022, 2024');
		$this->assertEquals('2020, 2022, 2024', $copyright3->year);
	}

	/**
	 * Test Copyright with various license URLs.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_handles_various_license_urls(): void
	{
		// Arrange & Act - Creative Commons
		$copyright1 = new Copyright('John Doe', null, 'https://creativecommons.org/licenses/by/4.0/');
		$this->assertEquals('https://creativecommons.org/licenses/by/4.0/', $copyright1->license);

		// Arrange & Act - MIT License
		$copyright2 = new Copyright('Jane Smith', null, 'https://opensource.org/licenses/MIT');
		$this->assertEquals('https://opensource.org/licenses/MIT', $copyright2->license);

		// Arrange & Act - GPL
		$copyright3 = new Copyright('Bob Johnson', null, 'https://www.gnu.org/licenses/gpl-3.0.html');
		$this->assertEquals('https://www.gnu.org/licenses/gpl-3.0.html', $copyright3->license);
	}

	/**
	 * Test Copyright with organization as author.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_can_have_organization_as_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright('Acme Corporation', '2024');

		// Assert
		$this->assertEquals('Acme Corporation', $copyright->author);
	}

	/**
	 * Test Copyright with special characters in author.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_handles_special_characters_in_author(): void
	{
		// Arrange & Act
		$copyright = new Copyright('José García & Associates');

		// Assert
		$this->assertEquals('José García & Associates', $copyright->author);
	}

	/**
	 * Test Copyright with current year.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_with_current_year(): void
	{
		// Arrange
		$currentYear = date('Y');

		// Act
		$copyright = new Copyright('John Doe', $currentYear);

		// Assert
		$this->assertEquals($currentYear, $copyright->year);
	}

	/**
	 * Test Copyright with historical year.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_with_historical_year(): void
	{
		// Arrange & Act
		$copyright = new Copyright('Historical Society', '1995');

		// Assert
		$this->assertEquals('1995', $copyright->year);
	}

	/**
	 * Test Copyright with public domain license.
	 * Requirements: 4.1, 4.2
	 */
	public function test_copyright_with_public_domain_license(): void
	{
		// Arrange & Act
		$copyright = new Copyright('Public Domain Contributor', null, 'https://creativecommons.org/publicdomain/zero/1.0/');

		// Assert
		$this->assertEquals('https://creativecommons.org/publicdomain/zero/1.0/', $copyright->license);
	}

	/**
	 * Test Copyright serialization preserves all data.
	 * Requirements: 4.3
	 */
	public function test_copyright_serialization_preserves_all_data(): void
	{
		// Arrange
		$copyright = new Copyright('Test Author', '2024', 'https://example.com/license');

		// Act - Serialize to array
		$array = $copyright->toArray();

		// Assert - All data preserved in array
		$this->assertEquals('Test Author', $array['author']);
		$this->assertEquals('2024', $array['year']);
		$this->assertEquals('https://example.com/license', $array['license']);

		// Act - Serialize to XML
		$document = new DOMDocument('1.0', 'UTF-8');
		$xmlElement = CopyrightParser::toXML($copyright, $document);
		$document->appendChild($xmlElement);
		$xml = $document->saveXML();

		// Assert - All data preserved in XML
		$this->assertStringContainsString('author="Test Author"', $xml);
		$this->assertStringContainsString('<year>2024</year>', $xml);
		$this->assertStringContainsString('<license>https://example.com/license</license>', $xml);
	}

	/**
	 * Property test: Copyright requires author parameter.
	 *
	 * **Feature: gpx-schema-compliance, Property 12: Copyright requires author**
	 * **Validates: Requirements 4.1**
	 */
	public function test_property_copyright_requires_author(): void
	{
		// This property is enforced by PHP's type system
		// Attempting to call new Copyright() without parameters will throw TypeError

		// We verify this by checking that TypeError is thrown
		$exceptionThrown = false;

		try {
			new Copyright();
		} catch (TypeError $e) {
			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown, 'TypeError should be thrown when creating Copyright without author');
	}

	/**
	 * Property test: Copyright author validation.
	 *
	 * **Feature: gpx-schema-compliance, Property 13: Copyright author validation**
	 * **Validates: Requirements 4.2**
	 */
	public function test_property_copyright_author_validation(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements(['', '   ', "\t", "\n", "  \t\n  "]),
			)
			->withMaxSize(100)
			->then(function ($emptyAuthor): void {
				// For any empty or whitespace-only author string,
				// Copyright construction should throw InvalidArgumentException

				$exceptionThrown = false;
				$exceptionMessage = '';

				try {
					new Copyright($emptyAuthor);
				} catch (InvalidArgumentException $e) {
					$exceptionThrown = true;
					$exceptionMessage = $e->getMessage();
				}

				// Assert exception was thrown
				$this->assertTrue(
					$exceptionThrown,
					'InvalidArgumentException should be thrown for empty/whitespace author',
				);

				// Assert error message contains required information
				$this->assertStringContainsString('Copyright author', $exceptionMessage);
				$this->assertStringContainsString('required', $exceptionMessage);
				$this->assertStringContainsString('cannot be empty', $exceptionMessage);
			});
	}

	/**
	 * Property test: Copyright accepts valid non-empty author strings.
	 *
	 * **Feature: gpx-schema-compliance, Property 13: Copyright author validation**
	 * **Validates: Requirements 4.2**
	 */
	public function test_property_copyright_accepts_valid_authors(): void
	{
		$this
			->withRand('mt_rand')
			->forAll(
				Generators::elements([
					'John Doe',
					'Jane Smith',
					'Acme Corporation',
					'José García & Associates',
					'Test Author 123',
					'A',
					'Very Long Author Name With Many Words And Characters',
					'Author-With-Dashes',
					'Author_With_Underscores',
					'Author.With.Dots',
					'Author (with parentheses)',
					'Author [with brackets]',
					'Author, Inc.',
					'Author & Co.',
					'Author @ Company',
					'Author #1',
					'Author $pecial',
					'Author 100%',
					'Author™',
					'Author©',
					'Author®',
				]),
			)
			->withMaxSize(100)
			->then(function ($validAuthor): void {
				// For any valid non-empty author string,
				// Copyright construction should succeed

				$copyright = new Copyright($validAuthor);

				// Assert copyright was created successfully
				$this->assertInstanceOf(Copyright::class, $copyright);
				$this->assertEquals($validAuthor, $copyright->author);
			});
	}
}
