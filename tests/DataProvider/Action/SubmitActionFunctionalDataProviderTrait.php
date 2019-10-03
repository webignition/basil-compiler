<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\DataProvider\Action;

use webignition\BasilModelFactory\Action\ActionFactory;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (submit), form submit button' => [
                'action' => $actionFactory->createFromActionString('submit "#form input[type=\'submit\']"'),
                'fixture' => '/action-click-submit.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$this->assertEquals("Click", self::$client->getTitle());',
                    '$submitButton = $crawler->filter(\'#form input[type="submit"]\')->getElement(0);',
                    '$form = $crawler->filter(\'#form\')->getElement(0);',
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'));',
                    '$this->assertEquals("false", $form->getAttribute(\'data-submitted\'));',
                ],
                'additionalTeardownStatements' => [
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'));',
                    '$this->assertEquals("true", $form->getAttribute(\'data-submitted\'));',
                ],
            ],
            'interaction action (submit), form' => [
                'action' => $actionFactory->createFromActionString('submit "#form"'),
                'fixture' => '/action-click-submit.html',
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'HAS' => '$has',
                    'ELEMENT' => '$element',
                ],
                'additionalSetupStatements' => [
                    '$this->assertEquals("Click", self::$client->getTitle());',
                    '$submitButton = $crawler->filter(\'#form input[type="submit"]\')->getElement(0);',
                    '$form = $crawler->filter(\'#form\')->getElement(0);',
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'));',
                    '$this->assertEquals("false", $form->getAttribute(\'data-submitted\'));',
                ],
                'additionalTeardownStatements' => [
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'));',
                    '$this->assertEquals("true", $form->getAttribute(\'data-submitted\'));',
                ],
            ],
        ];
    }
}
