
<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__)
	->exclude(['vendor', 'docs', 'coverage'])
	->name('*.php')
	->ignoreDotFiles(true)
	->ignoreVCS(true);

return (new PhpCsFixer\Config())
	->setRiskyAllowed(true)
	->setRules([
		'@PSR12' => true,
		'@PHP80Migration' => true,
		'@PHP81Migration' => true,
		'@PHP82Migration' => true,
		'@PHP83Migration' => true,
		'@PHP84Migration' => true,

		// Strict types
		'declare_strict_types' => true,

		// Array syntax
		'array_syntax' => ['syntax' => 'short'],

		// Imports
		'ordered_imports' => [
			'imports_order' => ['class', 'function', 'const'],
			'sort_algorithm' => 'alpha'
		],
		'no_unused_imports' => true,
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => true,
			'import_functions' => true,
		],

		// Modern PHP
		'modernize_types_casting' => true,
		'no_alias_functions' => true,
		'no_php4_constructor' => true,
		'nullable_type_declaration_for_default_null_value' => true,

		// Strict comparison
		'strict_comparison' => true,
		'strict_param' => true,

		// Return types
		'return_type_declaration' => ['space_before' => 'none'],
		'void_return' => true,

		// Visibility
		'visibility_required' => [
			'elements' => ['property', 'method', 'const']
		],

		// Whitespace
		'blank_line_after_namespace' => true,
		'blank_line_after_opening_tag' => true,
		'blank_line_before_statement' => [
			'statements' => ['return', 'throw', 'try']
		],
		'no_extra_blank_lines' => [
			'tokens' => ['extra', 'throw', 'use']
		],

		// Comments
		'single_line_comment_style' => true,
		'no_empty_comment' => true,

		// Control structures
		'no_alternative_syntax' => true,
		'no_superfluous_elseif' => true,
		'no_useless_else' => true,

		// Functions
		'function_declaration' => ['closure_function_spacing' => 'one'],
		'method_argument_space' => [
			'on_multiline' => 'ensure_fully_multiline'
		],

		// Classes
		'class_attributes_separation' => [
			'elements' => [
				'method' => 'one',
				'property' => 'one',
			]
		],
		'final_internal_class' => true,

		// Other
		'concat_space' => ['spacing' => 'one'],
		'no_trailing_comma_in_singleline' => true,
		'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
	])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
