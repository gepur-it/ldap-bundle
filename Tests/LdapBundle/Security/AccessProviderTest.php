<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 10.11.17
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;
use GepurIt\LdapBundle\Repository\LdapRoleAccessRepository;
use GepurIt\LdapBundle\Security\AccessProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessProviderTest
 * @package LdapBundle\Security
 */
class AccessProviderTest extends TestCase
{

    public function testGetResourceAccessMask()
    {
        $permissionOne = 1;
        $permissionTwo = 6;
        $expectedResult = 1 | 6;

        $resourceName = 'resource';
        $resource = $this->getResourceMock();
        $resource->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceName);

        $roleAccessOne = $this->getRoleAccessMock();
        $roleAccessOne->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);

        $roleAccessOne->expects($this->once())
            ->method('getPermissionMask')
            ->willReturn($permissionOne);

        $roleAccessTwo = $this->getRoleAccessMock();
        $roleAccessTwo->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);

        $roleAccessTwo->expects($this->once())
            ->method('getPermissionMask')
            ->willReturn($permissionTwo);

        $token = $this->getTokenInterfaceMock();
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())
            ->method('findByToken')
            ->with($token)
            ->willReturn([$roleAccessOne, $roleAccessTwo]);

        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(LdapRoleAccess::class)
            ->willReturn($repository);

        $accessProvider = new AccessProvider($entityManager);

        $this->assertEquals($expectedResult, $accessProvider->getResourceAccessMask($resourceName, $token));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    private function getTokenInterfaceMock()
    {
        $mock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    '__toString',
                    'getRoles',
                    'getCredentials',
                    'getUser',
                    'setUser',
                    'getUsername',
                    'isAuthenticated',
                    'setAuthenticated',
                    'eraseCredentials',
                    'getAttributes',
                    'setAttributes',
                    'hasAttribute',
                    'getAttribute',
                    'setAttribute',
                    'serialize',
                    'unserialize',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    private function getEntityManagerMock()
    {
        $mock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRepository',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LdapRoleAccessRepository
     */
    private function getRepositoryMock()
    {
        $mock = $this->getMockBuilder(LdapRoleAccessRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'findByToken',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return LdapRoleAccess|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRoleAccessMock()
    {
        $mock = $this->getMockBuilder(LdapRoleAccess::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getResource',
                    'getPermissionMask'
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return LdapResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResourceMock()
    {
        $mock = $this->getMockBuilder(LdapResource::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getResource',
                ]
            )
            ->getMock();

        return $mock;
    }
}
