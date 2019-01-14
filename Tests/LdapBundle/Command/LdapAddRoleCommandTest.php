<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 15.11.17
 */

namespace GepurIt\LdapBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Command\LdapAddRoleCommand;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapAddRoleCommandTest
 * @package LdapBundle\Command
 */
class LdapAddRoleCommandTest extends TestCase
{
    public function testLdapAddRoleAlreadyExists()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $provider = $this->createMock(LdapGroupsProvider::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $addResourceCommand = new LdapAddRoleCommand($provider, $entityManager);

        $roleName = 'abc';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('role_name')
            ->willReturn($roleName);

        $repository = $this->createMock(LdapRoleRepository::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByRole')
            ->with($roleName)
            ->willReturn(true);

        $provider->expects($this->never())
            ->method('rememberGroup');

        $addResourceCommand->run($input, $output);
    }

    public function testLdapAddRole()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $provider = $this->createMock(LdapGroupsProvider::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $addResourceCommand = new LdapAddRoleCommand($provider, $entityManager);

        $roleName = 'abc';
        $input->expects($this->once())
            ->method('getArgument')
            ->with('role_name')
            ->willReturn($roleName);

        $repository = $this->createMock(LdapRoleRepository::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('existsByRole')
            ->with($roleName)
            ->willReturn(false);

        $provider->expects($this->once())
            ->method('rememberGroup')
            ->with($roleName)
        ;

        $addResourceCommand->run($input, $output);
    }
}
