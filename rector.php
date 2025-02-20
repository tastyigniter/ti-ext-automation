<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanConstReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: true)
    ->withPaths([
        __DIR__.'/resources',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    ->withSkip([
        BoolReturnTypeFromBooleanConstReturnsRector::class => [
            __DIR__.'/src/Classes/BaseCondition.php',
        ],
        ReturnNeverTypeRector::class => [
            __DIR__.'/src/Classes/BaseAction.php',
        ],
        ReturnTypeFromStrictConstantReturnRector::class => [
            __DIR__.'/src/Classes/BaseCondition.php',
        ],
        ReturnTypeFromStrictNewArrayRector::class,
        ReturnTypeFromReturnDirectArrayRector::class => [
            __DIR__.'/src/Classes/BaseAction.php',
            __DIR__.'/src/Classes/BaseCondition.php',
            __DIR__.'/src/Classes/BaseEvent.php',
            __DIR__.'/src/Classes/BaseModelAttributesCondition.php',
        ],
        RemoveUselessReturnTagRector::class => [
            __DIR__.'/src/Classes/BaseAction.php',
            __DIR__.'/src/Classes/BaseCondition.php',
            __DIR__.'/src/Classes/BaseEvent.php',
        ],
    ])
    ->withTypeCoverageLevel(100)
    ->withDeadCodeLevel(100)
    ->withCodeQualityLevel(100);
