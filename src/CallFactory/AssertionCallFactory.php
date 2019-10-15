<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
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
        VariableAssignment $variableAssignmentCall
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_TRUE_TEMPLATE
        );
    }

    public function createValueIsFalseAssertionCall(
        VariableAssignment $variableAssignmentCall
    ): CompilableSourceInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_FALSE_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $expectedValueCall
     * @param VariableAssignment $actualValueCall
     *
     * @return CompilableSourceInterface
     */
    public function createValuesAreEqualAssertionCall(
        VariableAssignment $expectedValueCall,
        VariableAssignment $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_EQUALS_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $expectedValueCall
     * @param VariableAssignment $actualValueCall
     *
     * @return CompilableSourceInterface
     */
    public function createValuesAreNotEqualAssertionCall(
        VariableAssignment $expectedValueCall,
        VariableAssignment $actualValueCall
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $expectedValueCall,
            $actualValueCall,
            self::ASSERT_NOT_EQUALS_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $needle
     * @param VariableAssignment $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueIncludesValueAssertionCall(
        VariableAssignment $needle,
        VariableAssignment $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $needle
     * @param VariableAssignment $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueNotIncludesValueAssertionCall(
        VariableAssignment $needle,
        VariableAssignment $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $needle
     * @param VariableAssignment $haystack
     *
     * @return CompilableSourceInterface
     */
    public function createValueMatchesValueAssertionCall(
        VariableAssignment $needle,
        VariableAssignment $haystack
    ): CompilableSourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            self::ASSERT_MATCHES_TEMPLATE
        );
    }

    /**
     * @param VariableAssignment $expectedValueCall
     * @param VariableAssignment $actualValueCall
     * @param string $assertionTemplate
     *
     * @return CompilableSourceInterface
     */
    private function createValueComparisonAssertionCall(
        VariableAssignment $expectedValueCall,
        VariableAssignment $actualValueCall,
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
            $expectedValueCall->getVariablePlaceholder(),
            $actualValueCall->getVariablePlaceholder()
        );

        return (new CompilableSource())
            ->withPredecessors([$expectedValueCall, $actualValueCall])
            ->withStatements([$assertionStatement])
            ->withCompilationMetadata($compilationMetadata);
    }

    private function createValueExistenceAssertionCall(
        VariableAssignment $assignmentCall,
        string $assertionTemplate
    ): CompilableSourceInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $assignmentCall->getVariablePlaceholder()
        );

        return (new CompilableSource())
            ->withStatements([$assertionStatement])
            ->withPredecessors([$assignmentCall]);
    }
}
