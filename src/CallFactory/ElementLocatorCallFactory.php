<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\PlaceholderFactory;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\DomElementLocator\ElementLocator;

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
     * @return CompilableSourceInterface
     */
    public function createConstructorCall(DomIdentifierInterface $elementIdentifier): CompilableSourceInterface
    {
        $elementLocator = $elementIdentifier->getLocator();

        $arguments = '\'' . $this->singleQuotedStringEscaper->escape($elementLocator) . '\'';

        $position = $elementIdentifier->getOrdinalPosition();
        if (null !== $position) {
            $arguments .= ', ' . $position;
        }

        $statement = sprintf(self::TEMPLATE, $arguments);

        $compilationMetadata = (new CompilationMetadata())->withClassDependencies(new ClassDependencyCollection([
            new ClassDependency(ElementLocator::class),
        ]));

        return (new CompilableSource())
            ->withStatements([$statement])
            ->withCompilationMetadata($compilationMetadata);
    }
}
