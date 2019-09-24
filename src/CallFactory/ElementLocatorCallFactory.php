<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\TranspilationResultInterface;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;
use webignition\BasilTranspiler\PlaceholderFactory;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\SymfonyDomCrawlerNavigator\Model\ElementLocator;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s)';

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
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return TranspilationResultInterface
     */
    public function createConstructorCall(DomIdentifierInterface $elementIdentifier): TranspilationResultInterface
    {
        $elementExpression = $elementIdentifier->getElementExpression();

        $arguments = '\'' . $this->singleQuotedStringEscaper->escape($elementExpression->getExpression()) . '\'';

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
            ]),
            new VariablePlaceholderCollection()
        );
    }
}
