<?php /** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Model;

use webignition\BasilTranspiler\Model\Statement;
use webignition\BasilTranspiler\Model\StatementCollection;

class StatementCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $statements, StatementCollection $expectedCollection)
    {
        $this->assertEquals($expectedCollection, new StatementCollection($statements));
    }

    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'statements' => [],
                'expectedCollection' => new StatementCollection(),
            ],
            'invalid items' => [
                'statements' => [
                    1,
                    true,
                    'statement',
                ],
                'expectedCollection' => new StatementCollection(),
            ],
            'valid items' => [
                'statements' => [
                    new Statement('content'),
                ],
                'expectedCollection' => new StatementCollection([
                    new Statement('content'),
                ]),
            ],
        ];
    }

    public function testAdd()
    {
        $collection = new StatementCollection();

        $this->assertEquals(new StatementCollection(), $collection);

        $collection->add(new Statement('content'));

        $this->assertEquals(
            new StatementCollection([
                new Statement('content'),
            ]),
            $collection
        );
    }

    public function testIterator()
    {
        $statements = [
            new Statement('content1'),
            new Statement('content2'),
            new Statement('content3'),
        ];

        $collection = new StatementCollection($statements);

        foreach ($collection as $index => $statement) {
            $expectedStatement = $statements[$index];

            $this->assertSame($expectedStatement, $statement);
        }
    }
}
