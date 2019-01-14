<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 08.12.17
 * Time: 14:52
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Security\UserProfileProvider;
use GepurIt\User\Entity\UserProfile;
use GepurIt\User\Repository\UserProfileRepository;
use GepurIt\User\Security\User;
use PHPUnit\Framework\TestCase;

/**
 * Class UserProfileProviderTest
 * @package LdapBundle\Security
 */
class UserProfileProviderTest extends TestCase
{

    public function testNewProfile()
    {
        $entity_manager = $this->getEntityManagerMock();
        $user = $this->getUserMock();
        $userProfileRepository = $this->getUserProfileRepositoryMock();

        $userProfileProvider = new UserProfileProvider($entity_manager);

        $entity_manager->expects($this->once())
            ->method('getRepository')
            ->with(UserProfile::class)
            ->willReturn($userProfileRepository);

        $userProfileRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $user->expects($this->once())
            ->method('getUserId')
            ->willReturn('ldapSid');

        $entity_manager->expects($this->once())
            ->method('persist');

        $entity_manager->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(UserProfile::class, $userProfileProvider->getProfile($user));
    }

    public function testGetProfile()
    {
        $entity_manager = $this->getEntityManagerMock();
        $user = $this->getUserMock();
        $userProfileRepository = $this->getUserProfileRepositoryMock();
        $userProfile = $this->getUserProfileMock();
        $userProfileProvider = new UserProfileProvider($entity_manager);

        $entity_manager->expects($this->once())
            ->method('getRepository')
            ->with(UserProfile::class)
            ->willReturn($userProfileRepository);

        $userProfileRepository->expects($this->once())
            ->method('find')
            ->willReturn($userProfile);

        $this->assertInstanceOf(UserProfile::class, $userProfileProvider->getProfile($user));
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

    /**
     * @return UserProfile|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserProfileMock()
    {
        $mock = $this->getMockBuilder(UserProfile::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * @return UserProfile|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUserProfileRepositoryMock()
    {
        $mock = $this->getMockBuilder(UserProfileRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'find',
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
                    'persist',
                    'flush'
                ]
            )
            ->getMock();

        return $mock;
    }
}
