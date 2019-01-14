<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 13:09
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use GepurIt\LdapBundle\Security\ApiKeyUserProvider;
use GepurIt\LdapBundle\Security\LdapUserProvider;
use GepurIt\User\Security\User;
use GepurIt\LdapBundle\Document\UserApiKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ApiKeyUserProviderTest
 * @package LdapBundle\Security
 */
class ApiKeyUserProviderTest extends TestCase
{
    public function testGetUsernameForApiKey()
    {
        /** @var LdapUserProvider|\PHPUnit_Framework_MockObject_MockObject $ldap */
        $ldap = $this->createMock(LdapUserProvider::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->createMock(DocumentRepository::class);
        /** @var UserApiKey|\PHPUnit_Framework_MockObject_MockObject $userApiKey */
        $userApiKey = $this->createMock(UserApiKey::class);

        $provider = new ApiKeyUserProvider($ldap, $documentManager);

        $documentManager->expects($this->once())
            ->method('getRepository')
            ->with(UserApiKey::class)
            ->willReturn($repository);

        $repository->expects($this->once())->method('find')->with('key')->willReturn($userApiKey);

        $userApiKey->expects($this->once())->method('updateLastActivity');

        $documentManager->expects($this->once())->method('persist')->with($userApiKey);
        $documentManager->expects($this->once())->method('flush')->with($userApiKey);

        $userApiKey->expects($this->once())->method('getUserName');

        $provider->getUsernameForApiKey('key');
    }

    public function testNotGetUsernameForApiKey()
    {
        /** @var LdapUserProvider|\PHPUnit_Framework_MockObject_MockObject $ldap */
        $ldap = $this->createMock(LdapUserProvider::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = $this->createMock(DocumentRepository::class);
        /** @var UserApiKey|\PHPUnit_Framework_MockObject_MockObject $userApiKey */
        $userApiKey = $this->createMock(UserApiKey::class);

        $provider = new ApiKeyUserProvider($ldap, $documentManager);

        $documentManager->expects($this->once())
            ->method('getRepository')
            ->with(UserApiKey::class)
            ->willReturn($repository);

        $repository->expects($this->once())->method('find')->with('key')->willReturn(null);

        $provider->getUsernameForApiKey('key');
    }

    public function testLoadUserByUsername()
    {
        /** @var LdapUserProvider|\PHPUnit_Framework_MockObject_MockObject $ldap */
        $ldap = $this->createMock(LdapUserProvider::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);

        $provider = new ApiKeyUserProvider($ldap, $documentManager);

        $ldap->expects($this->once())->method('loadUserByUsername')->with('username')->willReturn($user);

        $result = $provider->loadUserByUsername('username');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRefreshUser()
    {
        /** @var LdapUserProvider|\PHPUnit_Framework_MockObject_MockObject $ldap */
        $ldap = $this->createMock(LdapUserProvider::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $userInterface */
        $userInterface = $this->createMock(UserInterface::class);

        $provider = new ApiKeyUserProvider($ldap, $documentManager);

        $this->expectException(UnsupportedUserException::class);

        $provider->refreshUser($userInterface);
    }

    public function testSupportsClass()
    {
        /** @var LdapUserProvider|\PHPUnit_Framework_MockObject_MockObject $ldap */
        $ldap = $this->createMock(LdapUserProvider::class);
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);

        $provider = new ApiKeyUserProvider($ldap, $documentManager);

        $this->assertTrue($provider->supportsClass(User::class));
        $this->assertFalse($provider->supportsClass(\stdClass::class));
    }
}
