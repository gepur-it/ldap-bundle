<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 15.11.17
 */

namespace GepurIt\LdapBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Command\PullRolesCommand;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PullRolesCommandTest
 * @package LdapBundle\Command
 */
class PullRolesCommandTest extends TestCase
{
    /**
     *
     */
    public function testPullRoles()
    {
        $provider = $this->createMock(LdapGroupsProvider::class);
        $entityManager = $this->createMock(EntityManager::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $repository = $this->createMock(LdapRoleRepository::class);

        $groups = [
            'groupName1',
            'groupName2',
            'groupName3',
        ];

        $provider->expects($this->once())
            ->method('loadRemoteGroups')
            ->willReturn($groups);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);
        $repository->expects($this->at(0))
            ->method('existsByRole')
            ->with('groupName1')
            ->willReturn(true);
        $repository->expects($this->at(1))
            ->method('existsByRole')
            ->with('groupName2')
            ->willReturn(false);
        $repository->expects($this->at(2))
            ->method('existsByRole')
            ->with('groupName3')
            ->willReturn(true);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(LdapRole::class));
        $entityManager->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf(LdapRole::class));


        $command = new PullRolesCommand($provider, $entityManager);
        $command->run($input, $output);
    }
}
