<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModelFactory\Action\ActionFactory;

trait SetActionDataProviderTrait
{
    public function setActionDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'input action, element identifier, literal value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to "value"'
                ),
            ],
            'input action, element identifier, element value' => [
                'action' => new InputAction(
                    'set ".selector" to ".source"',
                    new DomIdentifier('.selector'),
                    new DomIdentifierValue(
                        new DomIdentifier('.source')
                    ),
                    '".selector" to ".source"'
                ),
            ],
            'input action, element identifier, attribute value' => [
                'action' => new InputAction(
                    'set ".selector" to ".source".attribute_name',
                    new DomIdentifier('.selector'),
                    new DomIdentifierValue(
                        (new DomIdentifier('.source'))->withAttributeName('attribute_name')
                    ),
                    '".selector" to ".source"'
                ),
            ],
            'input action, browser property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $browser.size'
                ),
            ],
            'input action, page property' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $page.url'
                ),
            ],
            'input action, environment value' => [
                'action' => $actionFactory->createFromActionString(
                    'set ".selector" to $env.KEY'
                ),
            ],
        ];
    }
}
