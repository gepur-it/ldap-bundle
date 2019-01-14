<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 10.11.17
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Ldap\LdapConnection;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;

/**
 * Class LdapGroupsProviderTest
 * @package LdapBundle\Security
 */
class LdapGroupsProviderTest extends TestCase
{
    public function testRememberGroup()
    {
        $ldap = $this->getLdapConnectionMock();
        $entityManager = $this->getEntityManagerMock();
        $group = 'group';

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(LdapRole::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $groupsProvider = new LdapGroupsProvider($ldap, $entityManager);
        $groupsProvider->rememberGroup($group);
    }

    public function testGetLocalGroup()
    {
        $ldap = $this->getLdapConnectionMock();
        $result = [];

        $repository = $this->getRepositoryMock();
        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);;

        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn($result);

        $groupsProvider = new LdapGroupsProvider($ldap, $entityManager);
        $this->assertEquals($result, $groupsProvider->getLocalGroups());
    }

    public function testForgetGroup()
    {
        $ldap = $this->getLdapConnectionMock();
        $groupName = 'group';

        $repository = $this->getRepositoryMock();
        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);;

        $group = $this->getLdapRoleMock();
        $repository->expects($this->once())
            ->method('findOneByRole')
            ->with($groupName)
            ->willReturn($group);

        $entityManager->expects($this->once())
            ->method('remove')
            ->with($group);

        $entityManager->expects($this->once())
            ->method('flush');

        $groupsProvider = new LdapGroupsProvider($ldap, $entityManager);
        $groupsProvider->forgetGroup($groupName);
    }

    public function testForgetIfNotFoundGroup()
    {
        $ldap = $this->getLdapConnectionMock();
        $groupName = 'group';

        $repository = $this->getRepositoryMock();
        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRole::class)
            ->willReturn($repository);;

        $repository->expects($this->once())
            ->method('findOneByRole')
            ->with($groupName)
            ->willReturn(null);

        $entityManager->expects($this->never())
            ->method('remove');

        $entityManager->expects($this->never())
            ->method('flush');

        $groupsProvider = new LdapGroupsProvider($ldap, $entityManager);
        $groupsProvider->forgetGroup($groupName);
    }

    public function testLoadRemoteGroups()
    {
        $ldap = $this->getLdapConnectionMock();
        $groupName = 'groupName';
        $entryOne = $this->getEntryMock();
        $entryOne->expects($this->once())
            ->method('hasAttribute')
            ->with('cn')
            ->willReturn(false);
        $entryOne->expects($this->never())
            ->method('getAttribute');

        $entryTwo = $this->getEntryMock();
        $entryTwo->expects($this->once())
            ->method('hasAttribute')
            ->with('cn')
            ->willReturn(true);
        $entryTwo->expects($this->once())
            ->method('getAttribute')
            ->willReturn([$groupName]);

        $query = $this->getQueryInterfaceMock();
        $query->expects($this->once())
            ->method('execute')
            ->willReturn([$entryOne, $entryTwo]);

        $ldap->expects($this->once())
            ->method('search')
            ->with('(objectCategory=group)')
            ->willReturn($query);

        $entityManager = $this->getEntityManagerMock();

        $groupsProvider = new LdapGroupsProvider($ldap, $entityManager);
        $this->assertEquals([$groupName], $groupsProvider->loadRemoteGroups());
    }

    /**
     * @return LdapConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLdapConnectionMock()
    {
        $mock = $this->getMockBuilder(LdapConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'search',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return LdapRole|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLdapRoleMock()
    {
        $mock = $this->getMockBuilder(LdapRole::class)
            ->disableOriginalConstructor()
            ->getMock();;

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueryInterface
     */
    private function getQueryInterfaceMock()
    {
        $mock = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'execute',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Entry
     */
    private function getEntryMock()
    {
        $mock = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'hasAttribute',
                    'getAttribute',
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
                    'getRepository',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return LdapRoleRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRepositoryMock()
    {
        $mock = $this->getMockBuilder(LdapRoleRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'findOneByRole',
                    'findAll',
                ]
            )
            ->getMock();

        return $mock;
    }
}
