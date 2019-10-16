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
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\TranspilerInterface;

class NamedDomIdentifierValueTranspiler implements TranspilerInterface
{
    private $domCrawlerNavigatorCallFactory;
    private $elementLocatorCallFactory;
    private $assertionCallFactory;
    private $webDriverElementInspectorCallFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        AssertionCallFactory $assertionCallFactory,
        WebDriverElementInspectorCallFactory $webDriverElementInspectorCallFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->assertionCallFactory = $assertionCallFactory;
        $this->webDriverElementInspectorCallFactory = $webDriverElementInspectorCallFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createTranspiler(): NamedDomIdentifierValueTranspiler
    {
        return new NamedDomIdentifierValueTranspiler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            AssertionCallFactory::createFactory(),
            WebDriverElementInspectorCallFactory::createFactory(),
            SingleQuotedStringEscaper::create()
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
        $hasAttribute = null !== $identifier->getAttributeName();

        $elementCallArguments = $this->domCrawlerNavigatorCallFactory->createElementCallArguments($identifier);

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

        return (new CompilableSource())
            ->withPredecessors([
                $elementExistsAssertion,
                $elementOrCollectionAssignment,
                $valueAssignment,
            ]);
    }
}
