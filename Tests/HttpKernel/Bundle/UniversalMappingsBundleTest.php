<?php

/*
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pecserke\Component\UniversalMappings\Tests\HttpKernel\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class UniversalMappingsBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $alias = 'test';

    /**
     * @var string
     */
    private $namespace = 'Test\Namespace';

    /**
     * @var UniversalMappingsBundleTest
     */
    private $bundle;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $ormChainDriverDefinition;

    /**
     * @var Definition
     */
    private $mongodbChainDriverDefinition;

    /**
     * @var Definition
     */
    private $couchdbChainDriverDefinition;

    protected function setUp()
    {
        $this->bundle = $this->getMockForAbstractClass('Pecserke\Component\UniversalMappings\HttpKernel\Bundle\UniversalMappingsBundle', array(), '', true, true, true, array('getAlias', 'getNamespace'));
        $this->bundle
            ->expects($this->any())
            ->method('getAlias')
            ->will($this->returnValue($this->alias))
        ;
        $this->bundle
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue($this->namespace))
        ;

        $this->container = new ContainerBuilder();


        $chainDriverClass = 'Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain';

        $this->container->setParameter('doctrine.default_entity_manager', 'default');
        $this->ormChainDriverDefinition = new Definition($chainDriverClass);
        $this->container->setDefinition('doctrine.orm.default_metadata_driver', $this->ormChainDriverDefinition);

        $this->container->setParameter('doctrine_mongodb.odm.default_document_manager', 'default');
        $this->mongodbChainDriverDefinition = new Definition($chainDriverClass);
        $this->container->setDefinition('doctrine_mongodb.odm.default_metadata_driver', $this->mongodbChainDriverDefinition);

        $this->container->setParameter('doctrine_couchdb.default_document_manager', 'default');
        $this->couchdbChainDriverDefinition = new Definition($chainDriverClass);
        $this->container->setDefinition('doctrine_couchdb.odm.default_metadata_driver', $this->couchdbChainDriverDefinition);
    }

    public function testBuildOrm()
    {
        $this->bundle->build($this->container);
        $this->container->setParameter('test.backend_type_orm', true);
        $this->container->compile();

        $calls = $this->container->getDefinition('doctrine.orm.default_metadata_driver')->getMethodCalls();

        $definitions = array();
        foreach ($calls as $call) {
            $this->assertEquals('addDriver', $call[0]);
            $this->assertEquals($this->namespace . '\Model', $call[1][1]);
            $definitions[] = $call[1][0];
        }

        $classes = array_map(
            function(Definition $definition) {
                return $definition->getClass();
            },
            $definitions
        );

        $this->assertEquals($classes, array(
            'Doctrine\ORM\Mapping\Driver\XmlDriver',
            'Doctrine\ORM\Mapping\Driver\YamlDriver'
        ));
    }

    public function testBuildMongoDB()
    {
        $this->bundle->build($this->container);
        $this->container->setParameter('test.backend_type_mongodb', true);
        $this->container->compile();

        $calls = $this->container->getDefinition('doctrine_mongodb.odm.default_metadata_driver')->getMethodCalls();

        $definitions = array();
        foreach ($calls as $call) {
            $this->assertEquals('addDriver', $call[0]);
            $this->assertEquals($this->namespace . '\Model', $call[1][1]);
            $definitions[] = $call[1][0];
        }

        $classes = array_map(
            function(Definition $definition) {
                return $definition->getClass();
            },
            $definitions
        );

        $this->assertEquals($classes, array(
            'Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver',
            'Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver'
        ));
    }

    public function testBuildCouchDB()
    {
        $this->bundle->build($this->container);
        $this->container->setParameter('test.backend_type_couchdb', true);
        $this->container->compile();

        $calls = $this->container->getDefinition('doctrine_couchdb.odm.default_metadata_driver')->getMethodCalls();

        $definitions = array();
        foreach ($calls as $call) {
            $this->assertEquals('addDriver', $call[0]);
            $this->assertEquals($this->namespace . '\Model', $call[1][1]);
            $definitions[] = $call[1][0];
        }

        $classes = array_map(
            function(Definition $definition) {
                return $definition->getClass();
            },
            $definitions
        );

        $this->assertEquals($classes, array(
            'Doctrine\ODM\CouchDB\Mapping\Driver\XmlDriver',
            'Doctrine\ODM\CouchDB\Mapping\Driver\YamlDriver'
        ));
    }
}
 