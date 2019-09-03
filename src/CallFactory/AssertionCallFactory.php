<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\Call\VariableAssignmentCall;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\VariableNames;

class AssertionCallFactory
{
    const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    const ASSERT_NULL_TEMPLATE = '%s->assertNull(%s)';
    const ASSERT_NOT_NULL_TEMPLATE = '%s->assertNotNull(%s)';
    const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';

    const VARIABLE_EXISTS_TEMPLATE = self::ASSERT_NOT_NULL_TEMPLATE;
    const VARIABLE_NOT_EXISTS_TEMPLATE = self::ASSERT_NULL_TEMPLATE;

    private $transpilationResultComposer;
    private $phpUnitTestCasePlaceholder;

    /**
     * @var string
     */
    private $attributeExistsTemplate = '';

    /**
     * @var string
     */
    private $attributeNotExistsTemplate = '';

    public function __construct(TranspilationResultComposer $transpilationResultComposer)
    {
        $this->transpilationResultComposer = $transpilationResultComposer;
        $this->phpUnitTestCasePlaceholder = new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);

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
        return new AssertionCallFactory(
            TranspilationResultComposer::create()
        );
    }

    public function createValueExistsAssertionCall(
        VariableAssignmentCall $variableAssignmentCall
    ): TranspilationResultInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::VARIABLE_EXISTS_TEMPLATE
        );
    }

    public function createValueNotExistsAssertionCall(
        VariableAssignmentCall $variableAssignmentCall
    ): TranspilationResultInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::VARIABLE_NOT_EXISTS_TEMPLATE
        );
    }

    public function createValueIsTrueAssertionCall(
        VariableAssignmentCall $variableAssignmentCall
    ): TranspilationResultInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_TRUE_TEMPLATE
        );
    }

    public function createValueIsFalseAssertionCall(
        VariableAssignmentCall $variableAssignmentCall
    ): TranspilationResultInterface {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            self::ASSERT_FALSE_TEMPLATE
        );
    }

    /**
     * @param VariableAssignmentCall $expectedValueCall
     * @param VariableAssignmentCall $actualValueCall
     *
     * @return TranspilationResultInterface
     */
    public function createValuesAreEqualAssertionCall(
        VariableAssignmentCall $expectedValueCall,
        VariableAssignmentCall $actualValueCall
    ): TranspilationResultInterface {
        $assertionStatement = sprintf(
            self::ASSERT_EQUALS_TEMPLATE,
            $this->phpUnitTestCasePlaceholder,
            $expectedValueCall->getElementVariablePlaceholder(),
            $actualValueCall->getElementVariablePlaceholder()
        );

        $statements = array_merge(
            $expectedValueCall->getLines(),
            $actualValueCall->getLines(),
            [
                $assertionStatement,
            ]
        );

        $calls = [
            $expectedValueCall,
            $actualValueCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );
    }

    private function createValueExistenceAssertionCall(
        VariableAssignmentCall $variableAssignmentCall,
        string $assertionTemplate
    ): TranspilationResultInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variableAssignmentCall->getElementVariablePlaceholder()
        );

        $statements = array_merge(
            $variableAssignmentCall->getLines(),
            [
                $assertionStatement,
            ]
        );

        $calls = [
            $variableAssignmentCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $this->phpUnitTestCasePlaceholder,
            ])
        );
    }
}
