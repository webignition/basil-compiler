<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Functional\Assertion;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilTranspiler\Assertion\AssertionTranspiler;
use webignition\BasilTranspiler\Model\CompilationMetadataInterface;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExcludesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\ExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IncludesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\IsNotAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\MatchesAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\DataProvider\Assertion\NotExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilTranspiler\Tests\Functional\AbstractTestCase;

class AssertionTranspilerPassingAssertionsTest extends AbstractTestCase
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
        array $additionalSetupStatements = [],
        ?CompilationMetadataInterface $additionalCompilationMetadata = null
    ) {
        $compilableSource = $this->transpiler->transpile($assertion);

        $executableCall = $this->createExecutableCall(
            $compilableSource,
            $fixture,
            array_merge(self::VARIABLE_IDENTIFIERS, $variableIdentifiers),
            $additionalSetupStatements,
            [],
            $additionalCompilationMetadata
        );

        eval($executableCall);
    }
}
