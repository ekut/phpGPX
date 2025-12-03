<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
	])
	->withSkip([
		__DIR__ . '/vendor',
	])
	->withSets([
		LevelSetList::UP_TO_PHP_84,
		SetList::CODE_QUALITY,
		SetList::DEAD_CODE,
		SetList::EARLY_RETURN,
		SetList::TYPE_DECLARATION,
		SetList::PRIVATIZATION,
	])
	->withRules([
		AddVoidReturnTypeWhereNoReturnRector::class,
		TypedPropertyFromStrictConstructorRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
	])
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		typeDeclarations: true,
		privatization: true,
		earlyReturn: true,
		strictBooleans: true
	);
