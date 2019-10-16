<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
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
     * @return CompilableSourceInterface
     */
    public function createElementCallArguments(
        DomIdentifierInterface $elementIdentifier
    ): CompilableSourceInterface {
        $compilableSource = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $compilationMetadata = (new CompilationMetadata())->merge([
                $compilableSource->getCompilationMetadata(),
                $parentElementLocatorConstructorCall->getCompilationMetadata(),
            ]);

            $compilableSource = (new CompilableSource())
                ->withStatements([
                    sprintf(
                        '%s, %s',
                        (string) $compilableSource,
                        (string) $parentElementLocatorConstructorCall
                    ),
                ])
                ->withCompilationMetadata($compilationMetadata);
        }

        return $compilableSource;
    }
}
