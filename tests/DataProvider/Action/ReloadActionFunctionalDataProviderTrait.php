<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\VariableNames;

trait ReloadActionFunctionalDataProviderTrait
{
    public function reloadActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'reload action' => [
                'action' => $actionFactory->createFromActionString('reload'),
                'fixture' => '/action-wait-for.html',
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => '$crawler',
                ],
                'additionalUseStatements' => [],
                'additionalSetupStatements' => [
                    '$this->assertCount(0, $crawler->filter("#hello"));',
                    'usleep(100000);',
                    '$this->assertCount(1, $crawler->filter("#hello"));',
                ],
                'additionalTeardownStatements' => [
                    '$this->assertCount(0, $crawler->filter("#hello"));',
                    'usleep(100000);',
                    '$this->assertCount(1, $crawler->filter("#hello"));',
                ],
            ],
        ];
    }
}
