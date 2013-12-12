<?php

/*
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pecserke\Component\UniversalMappings\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterMappingsPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private $managerParameter;

    /**
     * @var Definition
     */
    private $chainDriverDefinition;

    /**
     * @var string
     */
    private $chainDriverServiceName;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $this->managerParameter = 'manager_parameter';

        $this->chainDriverDefinition = new Definition();
        $this->chainDriverServiceName = 'chain_service';

        $this->container->setParameter($this->managerParameter, $this->chainDriverServiceName);
        $this->container->setDefinition($this->chainDriverServiceName, $this->chainDriverDefinition);
    }

    public function testProcess()
    {
        $driver = new Definition();
        $namespace1 = 'some_namespace_1';
        $namespace2 = 'some_namespace_2';
        $pass = new RegisterMappingsPass($driver, array($namespace1, $namespace2), array($this->managerParameter), '%s');

        $pass->process($this->container);

        $this->assertEquals($this->chainDriverDefinition->getMethodCalls(), array(
            array('addDriver', array($driver, $namespace1)),
            array('addDriver', array($driver, $namespace2))
        ));
    }

    public function testProcessEnabledByParameter()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $enabledParameter = 'enabled_parameter';
        $pass = new RegisterMappingsPass($driver, array($namespace), array($this->managerParameter), '%s', $enabledParameter);

        $this->container->setParameter($enabledParameter, true); // value is not important, only parameter presence
        $pass->process($this->container);

        $this->assertEquals($this->chainDriverDefinition->getMethodCalls(), array(
            array('addDriver', array($driver, $namespace))
        ));
    }

    public function testProcessNotEnabledByParameter()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $enabledParameter = 'enabled_parameter';
        $pass = new RegisterMappingsPass($driver, array($namespace), array($this->managerParameter), '%s', $enabledParameter);

        // parameter $enabledParameter is not present
        $pass->process($this->container);

        $this->assertEmpty($this->chainDriverDefinition->getMethodCalls());
    }

    public function testProcessMoreManagersExistFirst()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $pass = new RegisterMappingsPass(
            $driver,
            array($namespace),
            array($this->managerParameter, 'this_parameter_does_not_exist'),
            '%s'
        );

        $pass->process($this->container);

        $this->assertNotEmpty($this->chainDriverDefinition->getMethodCalls());
    }

    public function testProcessMoreManagersNotExistFirst()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $pass = new RegisterMappingsPass(
            $driver,
            array($namespace),
            array('this_parameter_does_not_exist', $this->managerParameter),
            '%s'
        );

        $pass->process($this->container);

        $this->assertNotEmpty($this->chainDriverDefinition->getMethodCalls());
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testProcessManagersParameterNotExist()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $pass = new RegisterMappingsPass($driver, array($namespace), array('this_parameter_does_not_exist'), '%s');

        $pass->process($this->container);
    }

    public function testProcessPattern()
    {
        $driver = new Definition();
        $namespace = 'some_namespace';
        $pattern = '%s_service';
        $pass = new RegisterMappingsPass($driver, array($namespace), array('parameter'), $pattern);

        $this->container->setParameter('parameter', 'test');
        $this->container->setDefinition(
            sprintf($pattern, $this->container->getParameter('parameter')),
            $this->chainDriverDefinition
        );

        $pass->process($this->container);

        $this->assertNotEmpty($this->chainDriverDefinition->getMethodCalls());
    }
}
