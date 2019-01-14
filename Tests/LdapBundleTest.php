<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 10:40
 */
namespace GepurIt\LdapBundle\Tests;

use GepurIt\LdapBundle\DependencyInjection\Compiler\EntryPointCompilerPass;
use GepurIt\LdapBundle\LdapBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LdapBundleTest
 * @package LdapBundle
 */
class LdapBundleTest extends TestCase
{
    public function testBundle()
    {
        $containerBuilder = $this->getContainerMock();
        $containerBuilder->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(EntryPointCompilerPass::class));

        $bundle = new LdapBundle();
        $bundle->build($containerBuilder);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | ContainerBuilder
     */
    public function getContainerMock()
    {
        return  $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'addCompilerPass',
            ])
            ->getMock();
    }
}
