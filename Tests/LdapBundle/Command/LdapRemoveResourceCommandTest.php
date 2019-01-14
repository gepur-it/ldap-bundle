<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 15.11.17
 */

namespace GepurIt\LdapBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Command\LdapRemoveResourceCommand;
use GepurIt\LdapBundle\Repository\LdapResourceRepository;
use GepurIt\LdapBundle\Security\LdapResourcesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapRemoveResourceCommandTest
 * @package LdapBundle\Command
 */
class LdapRemoveResourceCommandTest extends TestCase
{
    public function testRemoveNotExistsResource()
    {
        $provider = $this->createMock(LdapResourcesProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapResourceRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $resourceName = 'resourceName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('resource_name')
            ->willReturn($resourceName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->with($resourceName)
            ->willReturn(false);

        $provider->expects($this->never())
            ->method('removeResource');

        $command = new LdapRemoveResourceCommand($provider, $entityManager);
        $command->run($input, $output);
    }

    public function testRemoveNotSureResource()
    {
        $provider = $this->createMock(LdapResourcesProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapResourceRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $questionHelper = $this->createMock(QuestionHelper::class);

        $resourceName = 'resourceName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('resource_name')
            ->willReturn($resourceName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->with($resourceName)
            ->willReturn(true);

        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn(false);

        $provider->expects($this->never())
            ->method('removeResource');

        $command = new LdapRemoveResourceCommand($provider, $entityManager);
        $command->setHelperSet(new HelperSet(['question' => $questionHelper]));
        $command->run($input, $output);
    }

    public function testRemoveResource()
    {
        $provider = $this->createMock(LdapResourcesProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapResourceRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $questionHelper = $this->createMock(QuestionHelper::class);

        $resourceName = 'resourceName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('resource_name')
            ->willReturn($resourceName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByResource')
            ->with($resourceName)
            ->willReturn(true);

        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn(true);

        $provider->expects($this->once())
            ->method('removeResource')
            ->with($resourceName);

        $command = new LdapRemoveResourceCommand($provider, $entityManager);
        $command->setHelperSet(new HelperSet(['question' => $questionHelper]));
        $command->run($input, $output);
    }
}
