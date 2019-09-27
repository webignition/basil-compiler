<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait ClickActionFunctionalDataProviderTrait
{
    public function clickActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (click), link' => [
                'action' => $actionFactory->createFromActionString('click "#link-to-index"'),
                'fixture' => '/action-click-submit.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [
                    '$this->assertEquals("Click", self::$client->getTitle());',
                ],
                'additionalTeardownLines' => [
                    '$this->assertEquals("Test fixture web server default document", self::$client->getTitle());',
                ],
            ],
            'interaction action (click), submit button' => [
                'action' => $actionFactory->createFromActionString('click "#form input[type=\'submit\']"'),
                'fixture' => '/action-click-submit.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalUseStatements' => [],
                'additionalSetupLines' => [
                    '$this->assertEquals("Click", self::$client->getTitle());',
                    '$submitButton = $crawler->filter(\'#form input[type="submit"]\')->getElement(0);',
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-clicked\'));',
                ],
                'additionalTeardownLines' => [
                    '$this->assertEquals("true", $submitButton->getAttribute(\'data-clicked\'));',
                ],
            ],
        ];
    }
}
