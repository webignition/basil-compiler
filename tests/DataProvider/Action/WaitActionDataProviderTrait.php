<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\ElementExpression;
use webignition\BasilModel\Value\ElementExpressionType;
use webignition\BasilModelFactory\Action\ActionFactory;

trait WaitActionDataProviderTrait
{
    public function waitActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait action, literal' => [
                'action' => $actionFactory->createFromActionString('wait 30'),
            ],
            'wait action, element value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(
                        new DomIdentifier(
                            new ElementExpression('.duration-selector', ElementExpressionType::CSS_SELECTOR)
                        )
                    )
                ),
            ],
            'wait action, attribute value' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(
                        (new DomIdentifier(
                            new ElementExpression('.duration-selector', ElementExpressionType::CSS_SELECTOR)
                        ))->withAttributeName('attribute_name')
                    )
                ),
            ],
            'wait action, browser property' => [
                'action' => $actionFactory->createFromActionString('wait $browser.size'),
            ],
            'wait action, page property' => [
                'action' => $actionFactory->createFromActionString('wait $page.title'),
            ],
            'wait action, environment value' => [
                'action' => $actionFactory->createFromActionString('wait $env.DURATION'),
            ],
        ];
    }
}