<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\ElementCallArgumentFactory;
use webignition\BasilTranspiler\CallFactory\ElementLocatorCallFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierInterface;

class NamedDomIdentifierTranspiler implements TranspilerInterface
{
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertionCallFactory;
    private $webDriverElementInspectorCallFactory;
    private $singleQuotedStringEscaper;
    private $elementCallArgumentFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertionCallFactory $assertionCallFactory,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        ElementCallArgumentFactory $elementCallArgumentFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertionCallFactory = $assertionCallFactory;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->elementCallArgumentFactory = $elementCallArgumentFactory;
    }

    public static function createTranspiler(): NamedDomIdentifierTranspiler
    {
        return new NamedDomIdentifierTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertionCallFactory::createFactory(),
            WebDriverElementInspectorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create(),
            ElementCallArgumentFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof NamedDomIdentifierInterface;
    }

    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof NamedDomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();
        $hasAttribute = null !== $identifier->getAttributeName();

        $elementCallArguments = $this->elementCallArgumentFactory->createElementCallArguments($identifier);

        if (false === $model->asCollection()) {
            $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCallForTranspiledArguments(
                $elementCallArguments
            );

            $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCallForTranspiledArguments(
                $elementCallArguments
            );
        } else {
            $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments(
                $elementCallArguments
            );

            $findCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments(
                $elementCallArguments
            );
        }

        $hasAssignmentVariableExports = new VariablePlaceholderCollection();
        $hasPlaceholder = $hasAssignmentVariableExports->create('HAS');

        $hasAssignment = clone $hasCall;
        $hasAssignment->prependStatement(-1, $hasPlaceholder . ' = ');
        $hasAssignment = $hasAssignment->withMetadata(
            (new Metadata())
                ->merge([
                    $hasCall->getMetadata(),
                ])
                ->withVariableExports($hasAssignmentVariableExports)
        );

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = clone $findCall;
        $elementOrCollectionAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
        $elementOrCollectionAssignment = $elementOrCollectionAssignment->withMetadata(
            (new Metadata())
                ->merge([
                    $findCall->getMetadata(),
                ])
                ->withVariableExports($collectionAssignmentVariableExports)
        );

        $elementExistsAssertion = $this->assertionCallFactory->createValueIsTrueAssertionCall(
            $hasAssignment,
            $hasPlaceholder
        );

        $predecessors = [
            $elementExistsAssertion,
            $elementOrCollectionAssignment,
        ];

        if ($model->includeValue()) {
            if ($hasAttribute) {
                $valueAssignment = (new Source())
                    ->withStatements([
                        sprintf(
                            '%s = %s->getAttribute(\'%s\')',
                            $elementPlaceholder,
                            $elementPlaceholder,
                            $this->singleQuotedStringEscaper->escape((string) $identifier->getAttributeName())
                        )
                    ]);
            } else {
                $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);

                $valueAssignment = clone $getValueCall;
                $valueAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
            }

            $predecessors[] = $valueAssignment;
        }

        return (new Source())
            ->withPredecessors($predecessors);
    }
}
