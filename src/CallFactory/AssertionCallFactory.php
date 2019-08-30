<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholder;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\TranspilationResultComposer;
use webignition\BasilTranspiler\UnknownItemException;
use webignition\BasilTranspiler\VariableNames;

class AssertionCallFactory
{
    const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    const ASSERT_NULL_TEMPLATE = '%s->assertNull(%s)';
    const ASSERT_NOT_NULL_TEMPLATE = '%s->assertNotNull(%s)';

    const ELEMENT_EXISTS_TEMPLATE = self::ASSERT_TRUE_TEMPLATE;
    const ELEMENT_NOT_EXISTS_TEMPLATE = self::ASSERT_FALSE_TEMPLATE;
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

    public function createElementExistsAssertionCall(TranspilationResult $domCrawlerHasElementCall): TranspilationResult
    {
        return $this->createElementExistenceAssertionCall($domCrawlerHasElementCall, self::ELEMENT_EXISTS_TEMPLATE);
    }

    public function createElementNotExistsAssertionCall(
        TranspilationResult $domCrawlerHasElementCall
    ): TranspilationResult {
        return $this->createElementExistenceAssertionCall($domCrawlerHasElementCall, self::ELEMENT_NOT_EXISTS_TEMPLATE);
    }

    public function createValueExistsAssertionCall(
        TranspilationResult $variableAssignmentCall,
        VariablePlaceholder $variablePlaceholder
    ): TranspilationResult {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            $variablePlaceholder,
            self::VARIABLE_EXISTS_TEMPLATE
        );
    }

    public function createValueNotExistsAssertionCall(
        TranspilationResult $variableAssignmentCall,
        VariablePlaceholder $variablePlaceholder
    ): TranspilationResult {
        return $this->createValueExistenceAssertionCall(
            $variableAssignmentCall,
            $variablePlaceholder,
            self::VARIABLE_NOT_EXISTS_TEMPLATE
        );
    }

    /**
     * @param TranspilationResult $elementVariableAssignmentCall
     * @param string $attributeName
     *
     * @return TranspilationResult
     *
     * @throws UnknownItemException
     */
    public function createAttributeExistsAssertionCall(
        TranspilationResult $elementVariableAssignmentCall,
        string $attributeName
    ): TranspilationResult {
        return $this->createAttributeExistenceAssertionCall(
            $elementVariableAssignmentCall,
            $attributeName,
            $this->attributeExistsTemplate
        );
    }

    /**
     * @param TranspilationResult $elementVariableAssignmentCall
     * @param string $attributeName
     *
     * @return TranspilationResult
     *
     * @throws UnknownItemException
     */
    public function createAttributeNotExistsAssertionCall(
        TranspilationResult $elementVariableAssignmentCall,
        string $attributeName
    ): TranspilationResult {
        return $this->createAttributeExistenceAssertionCall(
            $elementVariableAssignmentCall,
            $attributeName,
            $this->attributeNotExistsTemplate
        );
    }

    private function createElementExistenceAssertionCall(
        TranspilationResult $domCrawlerHasElementCall,
        string $assertionTemplate
    ): TranspilationResult {
        $template = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            '%s'
        );

        return $domCrawlerHasElementCall->extend(
            $template,
            new UseStatementCollection(),
            new VariablePlaceholderCollection([
                $this->phpUnitTestCasePlaceholder,
            ])
        );
    }

    private function createValueExistenceAssertionCall(
        TranspilationResult $variableAssignmentCall,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): TranspilationResult {
        $variableCreationStatement = (string) $variableAssignmentCall;

        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $statements = [
            $variableCreationStatement,
            $assertionStatement,
        ];

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

    /**
     * @param TranspilationResult $elementVariableAssignmentCall
     * @param string $attributeName
     * @param string $assertionTemplate
     *
     * @return TranspilationResult
     *
     * @throws UnknownItemException
     */
    private function createAttributeExistenceAssertionCall(
        TranspilationResult $elementVariableAssignmentCall,
        string $attributeName,
        string $assertionTemplate
    ): TranspilationResult {
        $elementVariableAssignmentCallPlaceholders = $elementVariableAssignmentCall->getVariablePlaceholders();

        $elementPlaceholder = $elementVariableAssignmentCallPlaceholders->get('ELEMENT');
        $phpunitTesCasePlaceholder = $elementVariableAssignmentCallPlaceholders->get(VariableNames::PHPUNIT_TEST_CASE);

        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $phpunitTesCasePlaceholder,
            $elementPlaceholder,
            $attributeName
        );

        $statements = array_merge(
            $elementVariableAssignmentCall->getLines(),
            [
                $assertionStatement,
            ]
        );

        $calls = [
            $elementVariableAssignmentCall,
        ];

        return $this->transpilationResultComposer->compose(
            $statements,
            $calls,
            new UseStatementCollection(),
            new VariablePlaceholderCollection()
        );
    }
}
