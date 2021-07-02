<?php # -*- coding: utf-8 -*-

namespace Inpsyde\AGBConnectorTests;

use Mockery;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;
use PHPUnit\Framework\TestCase as PhpUniTestCase;
use Xpmock\Reflection;
use Xpmock\TestCaseTrait;

/**
 * Class Testcase
 */
class TestCase extends PhpUniTestCase
{
    use TestCaseTrait;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        tearDown();
    }

    /**
     * Build the Testee Mock Object
     *
     * Basic configuration available for all of the testee objects, call `getMock` to get the mock.
     *
     * @param string $className
     * @param array $constructorArguments
     * @param array $methods
     * @return MockBuilder
     */
    protected function buildTesteeMock($className, $constructorArguments, $methods)
    {
        $testee = $this->getMockBuilder($className);
        $constructorArguments
            ? $testee->setConstructorArgs($constructorArguments)
            : $testee->disableOriginalConstructor();

        $testee->onlyMethods($methods);

        return $testee;
    }

    /**
     * Retrieve a Testee Mock to Test Protected Methods
     *
     * return MockBuilder
     * @param string $className
     * @param array $constructorArguments
     * @param array $methods
     * @return Reflection
     */
    protected function buildTesteeMethodMock($className, $constructorArguments, $methods)
    {
        $testee = $this->buildTesteeMock($className, $constructorArguments, $methods)->getMock();

        return $this->proxyFor($testee);
    }

    /**
     * Create a proxy for a mocked class
     *
     * @param MockObject $testee
     * @return Reflection
     */
    protected function proxyFor(MockObject $testee)
    {
        return $this->reflect($testee);
    }
}
