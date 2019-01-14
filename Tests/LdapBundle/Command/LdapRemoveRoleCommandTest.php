<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 15.11.17
 */

namespace GepurIt\LdapBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Command\LdapRemoveRoleCommand;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapRemoveRoleCommandTest
 * @package LdapBundle\Command
 */
class LdapRemoveRoleCommandTest extends TestCase
{
    public function testRemoveNotExistsResource()
    {
        $provider = $this->createMock(LdapGroupsProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapRoleRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $roleName = 'roleName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('role_name')
            ->willReturn($roleName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByRole')
            ->with($roleName)
            ->willReturn(false);

        $provider->expects($this->never())
            ->method('forgetGroup');

        $command = new LdapRemoveRoleCommand($provider, $entityManager);
        $command->run($input, $output);
    }

    public function testRemoveNotSureResource()
    {
        $provider = $this->createMock(LdapGroupsProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapRoleRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $questionHelper = $this->createMock(QuestionHelper::class);

        $roleName = 'roleName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('role_name')
            ->willReturn($roleName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByRole')
            ->with($roleName)
            ->willReturn(true);

        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn(false);

        $provider->expects($this->never())
            ->method('forgetGroup');

        $command = new LdapRemoveRoleCommand($provider, $entityManager);
        $command->setHelperSet(new HelperSet(['question' => $questionHelper]));
        $command->run($input, $output);
    }

    public function testRemoveResource()
    {
        $provider = $this->createMock(LdapGroupsProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $repository = $this->createMock(LdapRoleRepository::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $questionHelper = $this->createMock(QuestionHelper::class);

        $roleName = 'roleName';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('role_name')
            ->willReturn($roleName);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByRole')
            ->with($roleName)
            ->willReturn(true);

        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn(true);

        $provider->expects($this->once())
            ->method('forgetGroup')
            ->with($roleName);

        $command = new LdapRemoveRoleCommand($provider, $entityManager);
        $command->setHelperSet(new HelperSet(['question' => $questionHelper]));
        $command->run($input, $output);
    }
}
