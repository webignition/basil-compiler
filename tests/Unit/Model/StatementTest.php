<?php

declare(strict_types=1);

namespace webignition\BasilTranspiler\Tests\Unit\Model;

use webignition\BasilTranspiler\Model\ClassDependencyCollection;
use webignition\BasilTranspiler\Model\Statement;
use webignition\BasilTranspiler\Model\VariablePlaceholderCollection;

class StatementTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $content = 'content';

        $statement = new Statement($content);

        $this->assertSame($content, $statement->getContent());
        $this->assertEquals(new ClassDependencyCollection(), $statement->getClassDependencies());
        $this->assertEquals(new VariablePlaceholderCollection(), $statement->getVariableDependencies());
        $this->assertEquals(new VariablePlaceholderCollection(), $statement->getVariableExports());
    }

    public function testWithClassDependencies()
    {
        $statement = new Statement('content');

        $this->assertEquals(new ClassDependencyCollection(), $statement->getClassDependencies());

        $classDependencies = new ClassDependencyCollection();

        $mutatedStatement = $statement->withClassDependencies($classDependencies);

        $this->assertNotSame($statement, $mutatedStatement);
        $this->assertSame($classDependencies, $mutatedStatement->getClassDependencies());
    }

    public function testWithVariableDependencies()
    {
        $statement = new Statement('content');

        $this->assertEquals(new VariablePlaceholderCollection(), $statement->getVariableDependencies());

        $variableDependencies = new VariablePlaceholderCollection();

        $mutatedStatement = $statement->withVariableDependencies($variableDependencies);

        $this->assertNotSame($statement, $mutatedStatement);
        $this->assertSame($variableDependencies, $mutatedStatement->getVariableDependencies());
    }

    public function testWithVariableExports()
    {
        $statement = new Statement('content');

        $this->assertEquals(new VariablePlaceholderCollection(), $statement->getVariableExports());

        $variableExports = new VariablePlaceholderCollection();

        $mutatedStatement = $statement->withVariableExports($variableExports);

        $this->assertNotSame($statement, $mutatedStatement);
        $this->assertSame($variableExports, $mutatedStatement->getVariableExports());
    }
}
