<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class ElementCallArgumentFactory
{
    private $elementLocatorCallFactory;

    public function __construct(ElementLocatorCallFactory $elementLocatorCallFactory)
    {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
    }

    public static function createFactory(): ElementCallArgumentFactory
    {
        return new ElementCallArgumentFactory(
            ElementLocatorCallFactory::createFactory()
        );
    }

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return SourceInterface
     */
    public function createElementCallArguments(
        DomIdentifierInterface $elementIdentifier
    ): SourceInterface {
        $compilableSource = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $compilationMetadata = (new Metadata())->merge([
                $compilableSource->getMetadata(),
                $parentElementLocatorConstructorCall->getMetadata(),
            ]);

            $compilableSource = (new Source())
                ->withStatements([
                    sprintf(
                        '%s, %s',
                        (string) $compilableSource,
                        (string) $parentElementLocatorConstructorCall
                    ),
                ])
                ->withMetadata($compilationMetadata);
        }

        return $compilableSource;
    }
}
