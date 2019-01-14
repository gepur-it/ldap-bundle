<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 13.11.17
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Documents\CustomUser;
use GepurIt\LdapBundle\Security\LdapUserProvider;
use GepurIt\LdapBundle\Security\UserProfileProvider;
use GepurIt\User\Security\User;
use GepurIt\LdapBundle\Entry\EntryHelper;
use GepurIt\LdapBundle\Ldap\LdapConnection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\CollectionInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class LdapUserProviderTest
 * @package LdapBundle\Security
 */
class LdapUserProviderTest extends TestCase
{
    /**
     * @param $checkClass
     * @param $supports
     * @dataProvider supportsProvider
     */
    public function testSupportsClass($checkClass, $supports)
    {
        $groupName = 'Шаблоны';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $this->assertEquals($supports, $userProvider->supportsClass($checkClass));
    }

    public function testRefreshUserException()
    {
        $groupName = 'Шаблоны';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $this->expectException(UnsupportedUserException::class);
        $invalidUser = new \Symfony\Component\Security\Core\User\User('username', 'password');
        $userProvider->refreshUser($invalidUser);
    }

    public function testRefreshUser()
    {
        $groupName = 'Шаблоны';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $user = $this->getUserMock();
        $user->expects($this->once())
            ->method('getLogin')
            ->willReturn('login');
        $user->expects($this->once())
            ->method('getUserId')
            ->willReturn('ldapSid');
        $user->expects($this->once())
            ->method('getName')
            ->willReturn('name');
        $user->expects($this->once())
            ->method('getRoles')
            ->willReturn([]);
        $this->assertInstanceOf(User::class, $userProvider->refreshUser($user));
    }

    /**
     * @return array
     */
    public function supportsProvider()
    {
        return [
            'user supports' => [
                'check class' => User::class,
                'supports' => true,
            ],
            'another user not supports' => [
                'check class' => CustomUser::class,
                'supports' => false,
            ],
            'some different user not supports' => [
                'check class' => \Symfony\Component\Security\Core\User\User::class,
                'supports' => false,
            ],
            'abracadabra' => [
                'check class' => 'abracadabra',
                'supports' => false,
            ],
        ];
    }

    public function testGetActiveUsersGroupHasNoMemberOf()
    {
        $groupName = 'Шаблоны';
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $search = $this->getQueryInterfaceMock();
        $ldap->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($search);

        $entriesCollection = $this->getEntriesCollectionMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn($entriesCollection);

        $entry = $this->getEntryMock();
        $entry->expects($this->once())
            ->method('hasAttribute')
            ->with('memberOf')
            ->willReturn(false);
        $entry->expects($this->never())
            ->method('getAttribute');

        $entriesCollection->expects($this->once())
            ->method('toArray')
            ->willReturn([$entry]);

        $this->assertEmpty($userProvider->getActiveUsers());
    }

    public function testGetActiveUsersGroupHasNoUidKey()
    {
        $groupName = 'Шаблоны';
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $search = $this->getQueryInterfaceMock();
        $ldap->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($search);

        $entriesCollection = $this->getEntriesCollectionMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn($entriesCollection);

        $entry = $this->getEntryMock();
        $entry->expects($this->at(0))
            ->method('hasAttribute')
            ->with('memberOf')
            ->willReturn(true);
        $entry->expects($this->at(1))
            ->method('hasAttribute')
            ->with(LdapUserProvider::DEFAULT_UID_KEY)
            ->willReturn(false);
        $entry->expects($this->never())
            ->method('getAttribute');

        $entriesCollection->expects($this->once())
            ->method('toArray')
            ->willReturn([$entry]);

        $this->assertEmpty($userProvider->getActiveUsers());
    }

    public function testGetActiveUsersGroupHasNoNeedAttribute()
    {
        $groupName = 'Шаблоны';
        $group = 'OU=somethingWrong';
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $search = $this->getQueryInterfaceMock();
        $ldap->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($search);

        $entriesCollection = $this->getEntriesCollectionMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn($entriesCollection);

        $entry = $this->getEntryMock();
        $entry->expects($this->at(0))
            ->method('hasAttribute')
            ->with('memberOf')
            ->willReturn(true);
        $entry->expects($this->at(1))
            ->method('hasAttribute')
            ->with(LdapUserProvider::DEFAULT_UID_KEY)
            ->willReturn(true);
        $entry->expects($this->once())
            ->method('getAttribute')
            ->with('memberOf')
            ->willReturn([$group])
        ;

        $entriesCollection->expects($this->once())
            ->method('toArray')
            ->willReturn([$entry]);

        $this->assertEmpty($userProvider->getActiveUsers());
    }

    public function testGetActiveUsers()
    {
        $groupName = 'Шаблоны';
        $group = 'OU='.$groupName;
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $search = $this->getQueryInterfaceMock();
        $ldap->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($search);

        $entriesCollection = $this->getEntriesCollectionMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn($entriesCollection);

        $entry = $this->getEntryMock();
        $entry->expects($this->at(0))
            ->method('hasAttribute')
            ->with('memberOf')
            ->willReturn(true);
        $entry->expects($this->at(1))
            ->method('hasAttribute')
            ->with(LdapUserProvider::DEFAULT_UID_KEY)
            ->willReturn(true);
        $entry->expects($this->at(2))
            ->method('getAttribute')
            ->with('memberOf')
            ->willReturn([$group]);

        $entriesCollection->expects($this->once())
            ->method('toArray')
            ->willReturn([$entry]);

        $helper->expects($this->once())
            ->method('convertToUser')
            ->willReturn($this->getUserMock());

        $result = $userProvider->getActiveUsers();

        $this->assertNotEmpty($result);
        $user = array_pop($result);
        $this->assertInstanceOf(\GepurIt\User\Security\User::class, $user);
    }

    public function testLoadUserByUsernameConnectionException()
    {
        $groupName = 'Шаблоны';
        $userName = 'userName';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($userName, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($userName);

        $ldap->expects($this->once())
            ->method('query')
            ->willThrowException(new ConnectionException(''));

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserByUsername($userName);
    }


    public function testLoadUserByUsernameNoUsersFound()
    {
        $groupName = 'Шаблоны';
        $userName = 'userName';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($userName, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($userName);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->willReturn($search);

        $search->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserByUsername($userName);
    }


    public function testLoadUserByUsernameTooMuchUsers()
    {
        $groupName = 'Шаблоны';
        $userName = 'userName';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($userName, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($userName);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->willReturn($search);

        $entryOne = $this->getEntryMock();
        $entryTwo = $this->getEntryMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn([$entryOne, $entryTwo]);

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserByUsername($userName);
    }

    public function testLoadUserByUsername()
    {
        $groupName = 'Шаблоны';
        $userName = 'userName';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($userName, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($userName);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->willReturn($search);

        $entryOne = $this->getEntryMock();
        $user = $this->getUserMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn([$entryOne]);
        $helper->expects($this->once())
            ->method('convertToUser')
            ->with($entryOne)
            ->willReturn($user);

        $this->assertInstanceOf(\GepurIt\User\Security\User::class, $userProvider->loadUserByUsername($userName));
    }


    public function testLoadUserBySidConnectionException()
    {
        $groupName = 'Шаблоны';
        $sid = 'sid';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($sid, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($sid);

        $ldap->expects($this->once())
            ->method('query')
            ->with("(objectSid={$sid})")
            ->willThrowException(new ConnectionException());

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserBySid($sid);
    }


    public function testLoadUserBySidNoUsersFound()
    {
        $groupName = 'Шаблоны';
        $sid = 'sid';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($sid, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($sid);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->with("(objectSid={$sid})")
            ->willReturn($search);

        $search->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserBySid($sid);
    }

    public function testLoadUserBySidMultipleUsersFound()
    {
        $groupName = 'Шаблоны';
        $sid = 'sid';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($sid, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($sid);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->with("(objectSid={$sid})")
            ->willReturn($search);
        $entryOne = $this->getEntryMock();
        $entryTwo = $this->getEntryMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn([$entryOne, $entryTwo]);

        $this->expectException(UsernameNotFoundException::class);

        $userProvider->loadUserBySid($sid);
    }

    public function testLoadUserBySid()
    {
        $groupName = 'Шаблоны';
        $sid = 'sid';
        $ldap = $this->getLdapConnectionMock();
        $helper = $this->getEntryHelperMock();
        /**
         * @var UserProfileProvider|\PHPUnit_Framework_MockObject_MockObject $mangerProvider
         */
        $mangerProvider = $this->getUserProfileProviderMock();

        $userProvider = new LdapUserProvider($ldap, $helper, $mangerProvider, $groupName);

        $ldap->expects($this->once())
            ->method('escape')
            ->with($sid, '', LdapInterface::ESCAPE_FILTER)
            ->willReturn($sid);

        $search = $this->getQueryInterfaceMock();

        $ldap->expects($this->once())
            ->method('query')
            ->with("(objectSid={$sid})")
            ->willReturn($search);

        $entryOne = $this->getEntryMock();
        $user = $this->getUserMock();
        $search->expects($this->once())
            ->method('execute')
            ->willReturn([$entryOne]);
        $helper->expects($this->once())
            ->method('convertToUser')
            ->with($entryOne)
            ->willReturn($user);

        $this->assertInstanceOf(User::class, $userProvider->loadUserBySid($sid));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LdapConnection
     */
    private function getLdapConnectionMock()
    {
        $mock = $this->getMockBuilder(LdapConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return EntryHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEntryHelperMock()
    {
        $mock = $this->getMockBuilder(EntryHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return EntryHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserProfileProviderMock()
    {
        $mock = $this->getMockBuilder(UserProfileProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CollectionInterface
     */
    private function getEntriesCollectionMock()
    {
        $mock = $this->getMockBuilder(CollectionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|QueryInterface
     */
    private function getQueryInterfaceMock()
    {
        $mock = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
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
            ->getMock();

        return $mock;
    }

    /**
     * @return User|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserMock()
    {
        $mock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}


