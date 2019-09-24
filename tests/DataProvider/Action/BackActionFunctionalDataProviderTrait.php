<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilTranspiler\VariableNames;

trait BackActionFunctionalDataProviderTrait
{
    public function backActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'back action' => [
                'action' => $actionFactory->createFromActionString('back'),
                'fixture' => '/index.html',
                'variableIdentifiers' => [
                    VariableNames::PANTHER_CRAWLER => '$crawler',
                ],
                'additionalUseStatements' => [],
                'additionalPreLines' => [
                    '$this->assertEquals("Test fixture web server default document", self::$client->getTitle());',
                    '$crawler = $crawler->filter(\'#link-to-basic\')->getElement(0)->click();',
                    '$this->assertEquals("A basic page", self::$client->getTitle());',
                ],
                'additionalPostLines' => [
                    '$this->assertEquals("Test fixture web server default document", self::$client->getTitle());',
                ],
            ],
        ];
    }
}