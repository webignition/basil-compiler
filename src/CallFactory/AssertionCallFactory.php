<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\Model\VariableAssignment;
use webignition\BasilTranspiler\VariableNames;

class AssertionCallFactory
{
    const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    const ASSERT_NULL_TEMPLATE = '%s->assertNull(%s)';
    const ASSERT_NOT_NULL_TEMPLATE = '%s->assertNotNull(%s)';
    const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';
    const ASSERT_NOT_EQUALS_TEMPLATE = '%s->assertNotEquals(%s, %s)';
    const ASSERT_STRING_CONTAINS_STRING_TEMPLATE = '%s->assertStringContainsString((string) %s, (string) %s)';
    const ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE = '%s->assertStringNotContainsString((string) %s, (string) %s)';
    const ASSERT_MATCHES_TEMPLATE = '%s->assertRegExp(%s, %s)';

    const VARIABLE_EXISTS_TEMPLATE = self::ASSERT_NOT_NULL_TEMPLATE;
    const VARIABLE_NOT_EXISTS_TEMPLATE = self::ASSERT_NULL_TEMPLATE;

    private $phpUnitTestCasePlaceholder;
    private $variableDependencies;

    /**
     * @var string
     */
    private $attributeExistsTemplate = '';

    /**
     * @var string
     */
    private $attributeNotExistsTemplate = '';

    public function __construct()
    {
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->phpUnitTestCasePlaceholder = $this->variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        $this->attributeExistsTemplate = sprintf(
            self::VARIABLE_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );

        $this->attributeNotExistsTemplate = sprintf(
            self::VARIABLE_NOT_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );
    }

    public static function createFactory(): AssertionCallFactory
    {
        return new AssertionCallFactory();
    }

    public function createValueIsTrueAssertionCall(
        CompilableSourceInterface $variableAssignmentCall,
        VariablePlaceholder $placeholder
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            $placeholder,
            self::ASSERT_TRUE_TEMPLATE
        );
    }

    public function createValueIsFalseAssertionCall(
        CompilableSourceInterface $variableAssignmentCall,
        VariablePlaceholder $placeholder
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            $placeholder,
            self::ASSERT_FALSE_TEMPLATE
        );
    }

    public function createValuesAreEqualAssertionCall(
        CompilableSourceInterface $expectedValueCall,
        CompilableSourceInterface $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_EQUALS_TEMPLATE
        );
    }

    public function createValuesAreNotEqualAssertionCall(
        CompilableSourceInterface $expectedValueCall,
        CompilableSourceInterface $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_NOT_EQUALS_TEMPLATE
        );
    }

    public function createValueIncludesValueAssertionCall(
        CompilableSourceInterface $needle,
        CompilableSourceInterface $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
        );
    }

    public function createValueNotIncludesValueAssertionCall(
        CompilableSourceInterface $needle,
        CompilableSourceInterface $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE
        );
    }

    public function createValueMatchesValueAssertionCall(
        CompilableSourceInterface $needle,
        CompilableSourceInterface $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_MATCHES_TEMPLATE
        );
    }

    private function createValueComparisonAssertionCall(
        CompilableSourceInterface $expectedValueCall,
        CompilableSourceInterface $actualValueCall,
        string $assertionTemplate
    ): CompilableSourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->withAdditionalItems([
            $this->phpUnitTestCasePlaceholder,
        ]);

        $compilationMetadata = (new CompilationMetadata())->withVariableDependencies($variableDependencies);

        $expectedValuePlaceholder = $expectedValueCall instanceof VariableAssignment
            ? $expectedValueCall->getVariablePlaceholder()
            : '';

        $actualValuePlaceholder = $actualValueCall instanceof VariableAssignment
            ? $actualValueCall->getVariablePlaceholder()
            : '';

        $assertionStatement = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValuePlaceholder,
            $actualValuePlaceholder
        );

        return (new CompilableSource())
            ->withPredecessors([$expectedValueCall, $actualValueCall])
            ->withStatements([$assertionStatement])
            ->withCompilationMetadata($compilationMetadata);
    }

    private function createValueExistenceAssertionCall(
        CompilableSourceInterface $assignmentCall,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): CompilableSourceInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $compilationMetadata = (new CompilationMetadata())
            ->withVariableDependencies($this->variableDependencies);

        return (new CompilableSource())
            ->withCompilationMetadata($compilationMetadata)
            ->withStatements([$assertionStatement])
            ->withPredecessors([$assignmentCall]);
    }
}
