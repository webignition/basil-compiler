<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\CompilableSource;
use webignition\BasilCompilationSource\CompilableSourceInterface;
use webignition\BasilCompilationSource\CompilationMetadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\Model\VariableAssignment;

class VariableAssignmentFactory
{
    private $assertionCallFactory;
    private $elementLocatorCallFactory;
    private $domCrawlerNavigatorCallFactory;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ElementLocatorCallFactory $elementLocatorCallFactory,
        DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
        $this->domCrawlerNavigatorCallFactory = $domCrawlerNavigatorCallFactory;
    }

    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory(
            AssertionCallFactory::createFactory(),
            ElementLocatorCallFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory()
        );
    }

    public function createForElement(
        DomIdentifierInterface $identifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $elementPlaceholder
    ) {
        $argumentsVariableExports = new VariablePlaceholderCollection([
            $elementLocatorPlaceholder,
        ]);

        $arguments = (new CompilableSource())
            ->withStatements([(string) $elementLocatorPlaceholder])
            ->withCompilationMetadata((new CompilationMetadata())->withVariableExports($argumentsVariableExports));

        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasOneCallForTranspiledArguments($arguments);
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCallForTranspiledArguments($arguments);

        return $this->createForElementOrCollection(
            $identifier,
            $elementLocatorPlaceholder,
            $elementPlaceholder,
            $hasCall,
            $findCall
        );
    }

    public function createForValueAccessor(
        CompilableSourceInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): CompilableSourceInterface {
        $assignment = clone $accessor;
        $assignment->prependStatement(-1, $placeholder . ' = ');
        $assignment->appendStatement(-1, ' ?? ' . $default);

        $variableExports = new VariablePlaceholderCollection([
            $placeholder,
        ]);

        $assignment = $assignment->withCompilationMetadata(
            $assignment->getCompilationMetadata()->withAdditionalVariableExports($variableExports)
        );

        return (new CompilableSource())
            ->withPredecessors([$assignment])
            ->withStatements([
                sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder)
            ]);
    }

    public function createForElementCollection(
        DomIdentifierInterface $identifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $collectionPlaceholder
    ) {
        $argumentsVariableExports = new VariablePlaceholderCollection([$elementLocatorPlaceholder]);
        $argumentsCompilationMetadata = (new CompilationMetadata())->withVariableExports($argumentsVariableExports);

        $arguments = (new CompilableSource())
            ->withStatements([(string) $elementLocatorPlaceholder])
            ->withCompilationMetadata($argumentsCompilationMetadata);

        $hasCall = $this->domCrawlerNavigatorCallFactory->createHasCallForTranspiledArguments($arguments);
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCallForTranspiledArguments($arguments);

        return $this->createForElementOrCollection(
            $identifier,
            $elementLocatorPlaceholder,
            $collectionPlaceholder,
            $hasCall,
            $findCall
        );
    }

    private function createForElementOrCollection(
        DomIdentifierInterface $identifier,
        VariablePlaceholder $elementLocatorPlaceholder,
        VariablePlaceholder $returnValuePlaceholder,
        CompilableSourceInterface $hasCall,
        CompilableSourceInterface $findCall
    ) {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([
            $elementLocatorPlaceholder,
            $returnValuePlaceholder,
        ]);

        $elementLocatorAssignment = VariableAssignment::fromCompilableSource(
            $this->elementLocatorCallFactory->createConstructorCall($identifier),
            $elementLocatorPlaceholder
        );

        $hasPlaceholder = $variableExports->create('HAS');

        $hasAssignment = VariableAssignment::fromCompilableSource($hasCall, $hasPlaceholder);

        $elementExistsAssertion = $this->assertionCallFactory->createValueIsTrueAssertionCall(
            $hasAssignment,
            $hasPlaceholder
        );

        return (new VariableAssignment($returnValuePlaceholder))
            ->withPredecessors([
                $elementLocatorAssignment,
                $elementExistsAssertion,
                $findCall,
            ]);
    }
}
