<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 10.11.17
 */

namespace GepurIt\Tests\LdapBundle\Security;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Repository\LdapResourceRepository;
use GepurIt\LdapBundle\Security\LdapResourcesProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class LdapResourcesProviderTest
 * @package LdapBundle\Security
 */
class LdapResourcesProviderTest extends TestCase
{

    public function testCreateResource()
    {
        $entityManager = $this->getEntityManagerMock();
        $resourceProvider = new LdapResourcesProvider($entityManager);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(LdapResource::class));
        $entityManager->expects($this->once())
            ->method('flush');
        $resourceProvider->createResource('resource');
    }

    public function testRemoveResourceNotFound()
    {
        $resourceName = 'resource';
        $entityManager = $this->getEntityManagerMock();
        $resourceProvider = new LdapResourcesProvider($entityManager);
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneByResource')
            ->with($resourceName)
            ->willReturn(null);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapResource::class)
            ->willReturn($repository)
        ;
        $entityManager->expects($this->never())
            ->method('remove');
        $entityManager->expects($this->never())
            ->method('flush');
        $resourceProvider->removeResource('resource');
    }

    public function testRemoveResource()
    {
        $resourceName = 'resource';
        $resource = $this->getResourceMock();
        $entityManager = $this->getEntityManagerMock();
        $resourceProvider = new LdapResourcesProvider($entityManager);
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findOneByResource')
            ->with($resourceName)
            ->willReturn($resource);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapResource::class)
            ->willReturn($repository)
        ;
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($resource)
        ;
        $entityManager->expects($this->once())
            ->method('flush');
        $resourceProvider->removeResource('resource');
    }

    public function testHasResource()
    {
        $entityManager = $this->getEntityManagerMock();
        $repository = $this->getRepositoryMock();

        $resourceProvider = new LdapResourcesProvider($entityManager);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapResource::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->willReturn(true);

        $resourceProvider->hasResource('test');
    }

    /**
     * @return LdapResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResourceMock()
    {
        $mock = $this->getMockBuilder(LdapResource::class)
            ->disableOriginalConstructor()
            ->setMethods(
                []
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return LdapResourceRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepositoryMock()
    {
        $mock = $this->getMockBuilder(LdapResourceRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'findOneByResource',
                    'existsByResource',
                    'findAll',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntityManagerMock()
    {
        $mock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'persist',
                    'flush',
                    'remove',
                    'getRepository'
                ]
            )
            ->getMock();

        return $mock;
    }
}

