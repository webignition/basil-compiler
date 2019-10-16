<?php declare(strict_types=1);

namespace webignition\BasilTranspiler;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
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

    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$model instanceof NamedDomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();
        $hasAttribute = null !== $identifier->getAttributeName();

        $elementCallArguments = $this->elementCallArgumentFactory->createElementCallArguments($identifier);

        if ($hasAttribute) {
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
        $hasAssignment = $hasAssignment->withCompilationMetadata(
            (new CompilationMetadata())
                ->merge([
                    $hasCall->getCompilationMetadata(),
                ])
                ->withVariableExports($hasAssignmentVariableExports)
        );

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $elementOrCollectionAssignment = clone $findCall;
        $elementOrCollectionAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
        $elementOrCollectionAssignment = $elementOrCollectionAssignment->withCompilationMetadata(
            (new CompilationMetadata())
                ->merge([
                    $findCall->getCompilationMetadata(),
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
                $valueAssignment = (new CompilableSource())
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

        return (new CompilableSource())
            ->withPredecessors($predecessors);
    }
}
