<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\VariableNames;

trait WaitForActionFunctionalDataProviderTrait
{
    public function waitForActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'wait-for action, css selector' => [
                'action' => $actionFactory->createFromActionString('wait-for "#hello"'),
                'fixture' => '/action-wait-for.html',
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => '$crawler',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
                'additionalUseStatements' => [],
                'additionalPreLines' => [],
                'additionalPostLines' => [],
            ],
        ];
    }
}
