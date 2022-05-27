<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Arrays\DisallowImplicitArrayCreationSniff;
use SlevomatCodingStandard\Sniffs\Classes\DisallowLateStaticBindingForConstantsSniff;
use SlevomatCodingStandard\Sniffs\Classes\UselessLateStaticBindingSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullCoalesceOperatorSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\DeadCatchSniff;
use SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseFromSameNamespaceSniff;
use SlevomatCodingStandard\Sniffs\PHP\OptimizedFunctionsWithoutUnpackingSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessSemicolonSniff;
use SlevomatCodingStandard\Sniffs\Variables\DuplicateAssignmentToVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

# see https://github.com/symplify/easy-coding-standard
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        # pick anything from https://github.com/symplify/easy-coding-standard#use-prepared-checker-sets
        SetList::PSR_12,
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
        SetList::CONTROL_STRUCTURES,
    ]);

    $ecsConfig->skip([
        ReferenceUsedNamesOnlySniff::class . '.' . ReferenceUsedNamesOnlySniff::CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME_WITHOUT_NAMESPACE,
        ReferenceUsedNamesOnlySniff::class . '.' . ReferenceUsedNamesOnlySniff::CODE_PARTIAL_USE,

        'PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff.Found',

        # resolve later with strict_types
        DeclareStrictTypesFixer::class,
        StrictComparisonFixer::class,
        PhpUnitStrictFixer::class,
        StrictParamFixer::class,
        # breaks code
        UnusedUsesSniff::class . '.' . UnusedUsesSniff::CODE_MISMATCHING_CASE => [
            __DIR__ . '/tests/*',
        ],

        // must keep original format, to test @var and property type docs
        __DIR__ . '/tests/PropertyTypeReader/Fixture',
    ]);

    $ecsConfig->rule(MethodChainingIndentationFixer::class);

    $ecsConfig->ruleWithConfiguration(GeneralPhpdocAnnotationRemoveFixer::class, [
        'annotations' => ['author', 'package', 'group', 'autor', 'covers']
    ]);

    # add preslash to every native function, to speedup process, e.g. \count()
    $ecsConfig->ruleWithConfiguration(NativeFunctionInvocationFixer::class, [
        'include' => [NativeFunctionInvocationFixer::SET_ALL]
    ]);

    # limit line length to 120 chars
    $ecsConfig->rule(LineLengthFixer::class);

    # imports FQN names
    $ecsConfig->ruleWithConfiguration(ReferenceUsedNamesOnlySniff::class, [
        'searchAnnotations' => true,
        'allowFullyQualifiedGlobalClasses' => true,
        'allowFullyQualifiedGlobalFunctions' => true,
        'allowFullyQualifiedGlobalConstants' => true,
        'allowPartialUses' => false,
    ]);

    $ecsConfig->rules([
        # make @var annotation into doc block
        PhpdocLineSpanFixer::class,
        MethodChainingIndentationFixer::class,
        # array - item per line
        StandaloneLineInMultilineArrayFixer::class,
        # make @param, @return and @var format united
        ParamReturnAndVarTagMalformsFixer::class,
        # use 4 spaces to indent
        IndentationTypeFixer::class,
        # native functions should be casted in lowercase
        NativeFunctionCasingFixer::class,
        # import namespaces
        FullyQualifiedStrictTypesFixer::class,
        GlobalNamespaceImportFixer::class,
        # slevomat rules from ruleset.xml
        UseFromSameNamespaceSniff::class,
        DuplicateAssignmentToVariableSniff::class,
        OptimizedFunctionsWithoutUnpackingSniff::class,
        UselessSemicolonSniff::class,
        DeadCatchSniff::class,
        UselessVariableSniff::class,
        UselessParenthesesSniff::class,
        DisallowLateStaticBindingForConstantsSniff::class,
        UselessLateStaticBindingSniff::class,
        RequireNullCoalesceOperatorSniff::class,
        StaticClosureSniff::class,
        DisallowImplicitArrayCreationSniff::class,
    ]);
};
