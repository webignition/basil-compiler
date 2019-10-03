<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\CompilableSource;
use webignition\BasilTranspiler\Model\CompilableSourceInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\CompilationMetadata;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
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
        VariableAssignmentCall $variableAssignmentCall
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_TRUE_TEMPLATE
        );
    }

    public function createValueIsFalseAssertionCall(
        VariableAssignmentCall $variableAssignmentCall
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_FALSE_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $expectedValueCall
     * @param VariableAssignmentCall $actualValueCall
     *
     * @return CompilableSourceInterface
     */
    public function createValuesAreEqualAssertionCall(
        VariableAssignmentCall $expectedValueCall,
        VariableAssignmentCall $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_EQUALS_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $expectedValueCall
     * @param VariableAssignmentCall $actualValueCall
     *
     * @return CompilableSourceInterface
     */
    public function createValuesAreNotEqualAssertionCall(
        VariableAssignmentCall $expectedValueCall,
        VariableAssignmentCall $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_NOT_EQUALS_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $needle
     * @param VariableAssignmentCall $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueIncludesValueAssertionCall(
        VariableAssignmentCall $needle,
        VariableAssignmentCall $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $needle
     * @param VariableAssignmentCall $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueNotIncludesValueAssertionCall(
        VariableAssignmentCall $needle,
        VariableAssignmentCall $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $needle
     * @param VariableAssignmentCall $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueMatchesValueAssertionCall(
        VariableAssignmentCall $needle,
        VariableAssignmentCall $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_MATCHES_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $expectedValueCall
     * @param VariableAssignmentCall $actualValueCall
     * @param string $assertionTemplate
     *
     * @return CompilableSourceInterface
     */
    private function createValueComparisonAssertionCall(
        VariableAssignmentCall $expectedValueCall,
        VariableAssignmentCall $actualValueCall,
        string $assertionTemplate
    ): CompilableSourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->withAdditionalItems([
            $this->phpUnitTestCasePlaceholder,
        ]);

        $compilationMetadata = (new CompilationMetadata())->withVariableDependencies($variableDependencies);

        $assertionStatement = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValueCall->getElementVariablePlaceholder(),
            $actualValueCall->getElementVariablePlaceholder()
        );

        $statements = array_merge(
            $expectedValueCall->getStatements(),
            $actualValueCall->getStatements(),
            [
                $assertionStatement,
            ]
        );

        $compilableSource = new CompilableSource($statements);

        $compilableSource = $compilableSource->mergeCompilationData([
            $expectedValueCall->getCompilationMetadata(),
            $actualValueCall->getCompilationMetadata(),
            $compilationMetadata,
        ]);

        return $compilableSource;
    }

    private function createValueExistenceAssertionCall(
        VariableAssignmentCall $assignmentCall,
        string $assertionTemplate
    ): CompilableSourceInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $assignmentCall->getElementVariablePlaceholder()
        );

        $compilationMetadata = (new CompilationMetadata())->merge([
            $assignmentCall->getCompilationMetadata(),
        ]);

        $compilationMetadata = $compilationMetadata->withAdditionalVariableDependencies(
            new VariablePlaceholderCollection([$this->phpUnitTestCasePlaceholder])
        );

        $compilableSource = new CompilableSource(
            array_merge($assignmentCall->getStatements(), [$assertionStatement]),
            $compilationMetadata
        );

        return $compilableSource;
    }
}
