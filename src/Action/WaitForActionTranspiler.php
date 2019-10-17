<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\Action;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\BasilTranspiler\NonTranspilableModelException;
use webignition\BasilTranspiler\SingleQuotedStringEscaper;
use webignition\BasilTranspiler\TranspilerInterface;
use webignition\BasilTranspiler\VariableNames;

class WaitForActionTranspiler implements TranspilerInterface
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createTranspiler(): WaitForActionTranspiler
    {
        return new WaitForActionTranspiler(SingleQuotedStringEscaper::create());
    }

    public function handles(object $model): bool
    {
        return $model instanceof InteractionActionInterface && ActionTypes::WAIT_FOR === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function transpile(object $model): SourceInterface
    {
        if (!$model instanceof InteractionActionInterface) {
            throw new NonTranspilableModelException($model);
        }

        if (ActionTypes::WAIT_FOR !== $model->getType()) {
            throw new NonTranspilableModelException($model);
        }

        $identifier = $model->getIdentifier();

        if (!$identifier instanceof DomIdentifierInterface) {
            throw new NonTranspilableModelException($model);
        }

        $elementLocator = $identifier->getLocator();

        if ('/' === $elementLocator[0]) {
            throw new NonTranspilableModelException($model);
        }

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $compilationMetadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return (new Source())
            ->withStatements([
                sprintf(
                    '%s = %s->waitFor(\'%s\')',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $this->singleQuotedStringEscaper->escape($elementLocator)
                ),
            ])
            ->withMetadata($compilationMetadata);
    }
}
