<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitInternalClassFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestClassRequiresCoversFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(SetList::COMMON);
    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::ARRAY);
    $ecsConfig->dynamicSets(['@Symfony', '@PHP84Migration']);

    $ecsConfig->cacheDirectory('.ecs_cache');
    $ecsConfig->parallel();

    $ecsConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $ecsConfig->rules([
        DeclareStrictTypesFixer::class,
        OrderedImportsFixer::class,
        NoUnusedImportsFixer::class,
        StrictComparisonFixer::class,
        CleanNamespaceFixer::class,
        NoUnsetCastFixer::class,
        SimpleToComplexStringVariableFixer::class,
        PsrAutoloadingFixer::class,
    ]);

    $ecsConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'equal' => true,
        'identical' => true,
        'less_and_greater' => true,
    ]);

    $ecsConfig->ruleWithConfiguration(PhpdocAlignFixer::class, [
        'align' => 'left',
        'tags' => [
            'method',
            'param',
            'property',
            'return',
            'throws',
            'type',
            'var',
        ],
    ]);

    $ecsConfig->ruleWithConfiguration(NullableTypeDeclarationFixer::class, [
        'syntax' => 'union',
    ]);

    $ecsConfig->skip([
        __DIR__.'/src/Shared/Infrastructure/Persistence/Doctrine/ORMRepository.php',
        PhpUnitTestClassRequiresCoversFixer::class => ['*Test.php'],
        PhpUnitInternalClassFixer::class => ['*Test.php'],
        PhpUnitMethodCasingFixer::class => ['*Test.php'],
        NotOperatorWithSuccessorSpaceFixer::class,
        PhpdocToCommentFixer::class,
        PhpdocSummaryFixer::class,
        PhpdocLineSpanFixer::class,
        NoBlankLinesAfterPhpdocFixer::class,
    ]);
};
