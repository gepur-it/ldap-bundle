<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 15.11.17
 */

namespace GepurIt\LdapBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Command\LdapAddResourceCommand;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Repository\LdapResourceRepository;
use GepurIt\LdapBundle\Security\LdapResourcesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapAddResourceCommandTest
 * @package LdapBundle\Command
 */
class LdapAddResourceCommandTest extends TestCase
{


    public function testLdapAddResourceAlreadyExists()
    {
        $entityManager = $this->getEntityManagerMock();
        $resourcesProvider = $this->getLdapResourcesProviderMock();
        $addResourceCommand = new LdapAddResourceCommand($resourcesProvider, $entityManager);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $resourceName = 'abc';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('resource_name')
            ->willReturn($resourceName);

        $repository = $this->getRepositoryMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapResource::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->with($resourceName)
            ->willReturn(true);

        $resourcesProvider->expects($this->never())
            ->method('createResource');

        $addResourceCommand->run($input, $output);
    }

    public function testLdapAddResource()
    {
        $entityManager = $this->getEntityManagerMock();
        $resourcesProvider = $this->getLdapResourcesProviderMock();
        $addResourceCommand = new LdapAddResourceCommand($resourcesProvider, $entityManager);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $resourceName = 'abc';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('resource_name')
            ->willReturn($resourceName);

        $repository = $this->getRepositoryMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapResource::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->with($resourceName)
            ->willReturn(false);

        $resourcesProvider->expects($this->once())
            ->method('createResource')
            ->with($resourceName)
        ;

        $addResourceCommand->run($input, $output);
    }

    /**
     * @return LdapResourcesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLdapResourcesProviderMock()
    {
        $mock = $this->getMockBuilder(LdapResourcesProvider::class)
            ->disableOriginalConstructor()
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
            ->getMock();

        return $mock;
    }
}

