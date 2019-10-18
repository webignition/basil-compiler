<?php declare(strict_types=1);

namespace webignition\BasilTranspiler\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilTranspiler\VariableNames;

class AssertionCallFactory
{
    const ASSERT_TRUE_TEMPLATE = '%s->assertTrue(%s)';
    const ASSERT_FALSE_TEMPLATE = '%s->assertFalse(%s)';
    const ASSERT_NULL_TEMPLATE = '%s->assertNull(%s)';
    const ASSERT_NOT_NULL_TEMPLATE = '%s->assertNotNull(%s)';
    const ASSERT_EQUALS_TEMPLATE = '%s->assertEquals(%s, %s)';
    const ASSERT_NOT_EQUALS_TEMPLATE = '%s->assertNotEquals(%s, %s)';
    const ASSERT_STRING_CONTAINS_STRING_TEMPLATE = '%s->assertStringContainsString((string) %s, (string) %s)';
    const ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE = '%s->assertStringNotContainsString((string) %s, (string) %s)';
    const ASSERT_MATCHES_TEMPLATE = '%s->assertRegExp(%s, %s)';

    const VARIABLE_EXISTS_TEMPLATE = self::ASSERT_NOT_NULL_TEMPLATE;
    const VARIABLE_NOT_EXISTS_TEMPLATE = self::ASSERT_NULL_TEMPLATE;

    private $phpUnitTestCasePlaceholder;
    private $variableDependencies;

    /**
     * @var string
     */
    private $attributeExistsTemplate = '';

    /**
     * @var string
     */
    private $attributeNotExistsTemplate = '';

    public function __construct()
    {
        $this->variableDependencies = new VariablePlaceholderCollection();
        $this->phpUnitTestCasePlaceholder = $this->variableDependencies->create(VariableNames::PHPUNIT_TEST_CASE);

        $this->attributeExistsTemplate = sprintf(
            self::VARIABLE_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );

        $this->attributeNotExistsTemplate = sprintf(
            self::VARIABLE_NOT_EXISTS_TEMPLATE,
            '%s',
            '%s->getAttribute(\'%s\')'
        );
    }

    public static function createFactory(): AssertionCallFactory
    {
        return new AssertionCallFactory();
    }

    public function createValueIncludesValueAssertionCall(
        SourceInterface $needle,
        SourceInterface $haystack,
        VariablePlaceholder $needlePlaceholder,
        VariablePlaceholder $haystackPlaceholder
    ): SourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            $needlePlaceholder,
            $haystackPlaceholder,
            self::ASSERT_STRING_CONTAINS_STRING_TEMPLATE
        );
    }

    public function createValueNotIncludesValueAssertionCall(
        SourceInterface $needle,
        SourceInterface $haystack,
        VariablePlaceholder $needlePlaceholder,
        VariablePlaceholder $haystackPlaceholder
    ): SourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            $needlePlaceholder,
            $haystackPlaceholder,
            self::ASSERT_STRING_NOT_CONTAINS_STRING_TEMPLATE
        );
    }

    public function createValueMatchesValueAssertionCall(
        SourceInterface $needle,
        SourceInterface $haystack,
        VariablePlaceholder $needlePlaceholder,
        VariablePlaceholder $haystackPlaceholder
    ): SourceInterface {
        return $this->createValueComparisonAssertionCall(
            $needle,
            $haystack,
            $needlePlaceholder,
            $haystackPlaceholder,
            self::ASSERT_MATCHES_TEMPLATE
        );
    }

    public function createValueComparisonAssertionCall(
        SourceInterface $expectedValueCall,
        SourceInterface $actualValueCall,
        VariablePlaceholder $expectedValuePlaceholder,
        VariablePlaceholder $actualValuePlaceholder,
        string $assertionTemplate
    ): SourceInterface {
        $variableDependencies = new VariablePlaceholderCollection();
        $variableDependencies = $variableDependencies->withAdditionalItems([
            $this->phpUnitTestCasePlaceholder,
        ]);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        $assertionStatement = sprintf(
            $assertionTemplate,
            $this->phpUnitTestCasePlaceholder,
            $expectedValuePlaceholder,
            $actualValuePlaceholder
        );

        return (new Source())
            ->withPredecessors([$expectedValueCall, $actualValueCall])
            ->withStatements([$assertionStatement])
            ->withMetadata($metadata);
    }

    public function createValueExistenceAssertionCall(
        SourceInterface $assignmentCall,
        VariablePlaceholder $variablePlaceholder,
        string $assertionTemplate
    ): SourceInterface {
        $assertionStatement = sprintf(
            $assertionTemplate,
            (string) $this->phpUnitTestCasePlaceholder,
            (string) $variablePlaceholder
        );

        $metadata = (new Metadata())
            ->withVariableDependencies($this->variableDependencies);

        return (new Source())
            ->withMetadata($metadata)
            ->withStatements([$assertionStatement])
            ->withPredecessors([$assignmentCall]);
    }
}
