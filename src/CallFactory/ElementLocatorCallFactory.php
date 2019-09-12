<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\PlaceholderFactory;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;
use webignition\SymfonyDomCrawlerNavigator\Model\LocatorType;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s)';
    const REQUIRED_ARGUMENTS_TEMPLATE = '%s, \'%s\'';
    const CSS_SELECTOR_LOCATOR_TYPE = 'LocatorType::CSS_SELECTOR';
    const XPATH_EXPRESSION_LOCATOR_TYPE = 'LocatorType::XPATH_EXPRESSION';

    private $placeholderFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        PlaceholderFactory $placeholderFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ElementLocatorCallFactory
    {
        return new ElementLocatorCallFactory(
            PlaceholderFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    /**
     * @param ElementIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     */
    public function createConstructorCall(ElementIdentifierInterface $elementIdentifier): TranspilationResultInterface
    {
        $elementExpression = $elementIdentifier->getElementExpression();

        $locatorTypeArgument = ElementExpressionType::CSS_SELECTOR === $elementExpression->getType()
            ? self::CSS_SELECTOR_LOCATOR_TYPE
            : self::XPATH_EXPRESSION_LOCATOR_TYPE;

        $arguments = sprintf(
            self::REQUIRED_ARGUMENTS_TEMPLATE,
            $locatorTypeArgument,
            $this->singleQuotedStringEscaper->escape($elementExpression->getExpression())
        );

        $position = $elementIdentifier->getPosition();
        if (null !== $position) {
            $arguments .= ', ' . $position;
        }

        $content = sprintf(self::TEMPLATE, $arguments);

        return new TranspilationResult(
            [
                $content,
            ],
            new UseStatementCollection([
                new UseStatement(ElementLocator::class),
                new UseStatement(LocatorType::class),
            ]),
            new VariablePlaceholderCollection()
        );
    }
}
