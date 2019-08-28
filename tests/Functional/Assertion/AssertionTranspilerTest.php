<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Assertion;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Model\TranspilationResult;
use webignition\BasilTranspiler\Model\UseStatement;
use webignition\BasilTranspiler\Model\UseStatementCollection;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\Tests\Services\ExecutableCallFactory;
use webignition\BasilTranspiler\VariableNames;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class AssertionTranspilerTest extends AbstractTestCase
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$domCrawlerNavigator';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';

    const VARIABLE_IDENTIFIERS = [
        VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
    ];

    /**
     * @var AssertionTranspiler
     */
    private $transpiler;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = AssertionTranspiler::createTranspiler();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider transpileForPassingAssertionsDataProvider
     */
    public function testTranspileForPassingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        array $variableIdentifiers
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture
        );

        eval($executableCall);
    }

    public function transpileForPassingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo" exists'
                ),
                'variableIdentifiers' => [],
            ],
            'exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"#a-sibling".class exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                ],
            ],
            'exists comparison, environment examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.TEST1 exists'
                ),
                'variableIdentifiers' => [
                    'ENVIRONMENT_VARIABLE' => '$environmentVariable',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
            'exists comparison, browser object value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$browser.size exists'
                ),
                'variableIdentifiers' => [
                    'BROWSER_VARIABLE' => '$browserVariable',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'exists comparison, page object value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$page.title exists'
                ),
                'variableIdentifiers' => [
                    'PAGE_VARIABLE' => '$pageVariable',
                    VariableNames::PANTHER_CLIENT => 'self::$client',
                ],
            ],
            'not-exists comparison, element identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" not-exists'
                ),
                'variableIdentifiers' => [],
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"#a-sibling".invalid not-exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                ],
            ],
            'not-exists comparison, environment examined value' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.INVALID not-exists'
                ),
                'variableIdentifiers' => [
                    'ENVIRONMENT_VARIABLE' => '$environmentVariable',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
            ],
        ];
    }

    /**
     * @dataProvider transpileForFailingAssertionsDataProvider
     */
    public function testTranspileForFailingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        array $variableIdentifiers,
        string $expectedExpectationFailedExceptionMessage
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExpectationFailedExceptionMessage);

        eval($executableCall);
    }

    public function transpileForFailingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value, element does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'variableIdentifiers' => [],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".foo".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that null is not null.',
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/basic.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.FOO exists'
                ),
                'variableIdentifiers' => [
                    'ENVIRONMENT_VARIABLE' => '$environmentVariable',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that null is not null.',
            ],
        ];
    }

    private function createExecutableCall(
        TranspilationResult $transpilationResult,
        array $variableIdentifiers,
        string $fixture
    ): string {
        return $this->executableCallFactory->create(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            [
                '$crawler = self::$client->request(\'GET\', \'' . $fixture . '\'); ',
                '$domCrawlerNavigator = Navigator::create($crawler); ',
            ],
            new UseStatementCollection([
                new UseStatement(Navigator::class),
            ])
        );
    }
}
