<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Assertion;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExcludesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IncludesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsNotAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\MatchesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;
use webignition\BasilTranspiler\VariableNames;

class AssertionTranspilerTest extends AbstractTestCase
{
    use ExistsAssertionFunctionalDataProviderTrait;
    use NotExistsAssertionFunctionalDataProviderTrait;
    use IsAssertionFunctionalDataProviderTrait;
    use IsNotAssertionFunctionalDataProviderTrait;
    use IncludesAssertionFunctionalDataProviderTrait;
    use ExcludesAssertionFunctionalDataProviderTrait;
    use MatchesAssertionFunctionalDataProviderTrait;

    /**
     * @var AssertionTranspiler
     */
    private $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = AssertionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider existsAssertionFunctionalDataProvider
     * @dataProvider notExistsAssertionFunctionalDataProvider
     * @dataProvider isAssertionFunctionalDataProvider
     * @dataProvider isNotAssertionFunctionalDataProvider
     * @dataProvider includesAssertionFunctionalDataProvider
     * @dataProvider excludesAssertionFunctionalDataProvider
     * @dataProvider matchesAssertionFunctionalDataProvider
     */
    public function testTranspileForPassingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        array $variableIdentifiers,
        array $additionalPreLines = [],
        array $additionalUseStatements = []
    ) {
        $transpilationResult = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $transpilationResult,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $fixture,
            $additionalPreLines,
            [],
            $additionalUseStatements
        );

        eval($executableCall);
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
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector" exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '".selector".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '"h1".attribute_name exists'
                ),
                'variableIdentifiers' => [
                    'ELEMENT_LOCATOR' => '$elementLocator',
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    'EXAMINED_VALUE' => '$examinedValue',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createAssertableAssertionFromString(
                    '$env.FOO exists'
                ),
                'variableIdentifiers' => [
                    'EXAMINED_VALUE' => '$examinedValue',
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
        ];
    }
}
