<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Value;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\CallFactory\AssertionCallFactory;
use webignition\BasilTranspiler\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilTranspiler\CallFactory\ElementLocatorCallFactory;
use webignition\BasilTranspiler\CallFactory\WebDriverElementInspectorCallFactory;
use webignition\BasilTranspiler\Model\NamedDomIdentifierValue;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\TranspilerInterface;

class NamedDomIdentifierValueTranspiler implements TranspilerInterface
{
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertionCallFactory;
    private $webDriverElementInspectorCallFactory;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertionCallFactory $assertionCallFactory,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertionCallFactory = $assertionCallFactory;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
    }

    public static function createTranspiler(): NamedDomIdentifierValueTranspiler
    {
        return new NamedDomIdentifierValueTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertionCallFactory::createFactory(),
            WebDriverElementInspectorCallFactory::createFactory()
        );
    }

    public function handles(object $model): bool
    {
        return $model instanceof NamedDomIdentifierValue;
    }

    public function transpile(object $model): CompilableSourceInterface
    {
        if (!$model instanceof NamedDomIdentifierValue) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        $elementCallArguments = $this->domCrawlerNavigatorCallFactory->createElementCallArguments($identifier);

        $hasElementCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments(
            $elementCallArguments
        );

        $findElementCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments(
            $elementCallArguments
        );

        $hasAssignmentVariableExports = new VariablePlaceholderCollection();
        $hasPlaceholder = $hasAssignmentVariableExports->create('HAS');

        $hasAssignment = clone $hasElementCall;
        $hasAssignment->prependStatement(-1, $hasPlaceholder . ' = ');
        $hasAssignment = $hasAssignment->withCompilationMetadata(
            (new CompilationMetadata())
                ->merge([
                    $hasElementCall->getCompilationMetadata(),
                ])
                ->withVariableExports($hasAssignmentVariableExports)
        );

        $elementPlaceholder = $model->getPlaceholder();
        $collectionAssignmentVariableExports = new VariablePlaceholderCollection([
            $elementPlaceholder,
        ]);

        $collectionAssignment = clone $findElementCall;
        $collectionAssignment->prependStatement(-1, $elementPlaceholder . ' = ');
        $collectionAssignment = $collectionAssignment->withCompilationMetadata(
            (new CompilationMetadata())
                ->merge([
                    $findElementCall->getCompilationMetadata(),
                ])
                ->withVariableExports($collectionAssignmentVariableExports)
        );

        $elementExistsAssertion = $this->assertionCallFactory->createValueIsTrueAssertionCall(
            $hasAssignment,
            $hasPlaceholder
        );

        $getValueCall = $this->webDriverElementInspectorCallFactory->createGetValueCall($elementPlaceholder);

        $getValueAssignment = clone $getValueCall;
        $getValueAssignment->prependStatement(-1, $elementPlaceholder . ' = ');

        return (new CompilableSource())
            ->withPredecessors([
                $elementExistsAssertion,
                $collectionAssignment,
                $getValueAssignment,
            ]);
    }
}
