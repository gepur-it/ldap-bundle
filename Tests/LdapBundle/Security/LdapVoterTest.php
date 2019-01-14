<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 10.11.17
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\AccessProvider;
use GepurIt\LdapBundle\Security\LdapVoter;
use GepurIt\LdapBundle\Security\PermissionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class LdapVoterTest
 * @package LdapBundle\Security
 */
class LdapVoterTest extends TestCase
{
    public function testVoteOnAttributeGranted()
    {
        $accessProvider = $this->getAccessProviderMock();
        $permissionProvider = $this->getPermissionProviderMock();
        $token = $this->getTokenInterfaceMock();

        $accessMask = 15;
        $attribute = 'attribute';
        $subject = 'subject';

        $ldapVoter = new LdapVoter($accessProvider, $permissionProvider);
        $accessProvider->expects($this->once())
            ->method('getResourceAccessMask')
            ->with($subject, $token)
            ->willReturn($accessMask);
        $permissionProvider->expects($this->once())
            ->method('isGranted')
            ->with($attribute, $accessMask)
            ->willReturn(true);

        $this->assertEquals(Voter::ACCESS_GRANTED, $ldapVoter->vote($token, $subject, [$attribute]));
    }

    public function testVoteOnAttributeDenied()
    {
        $accessProvider = $this->getAccessProviderMock();
        $permissionProvider = $this->getPermissionProviderMock();
        $token = $this->getTokenInterfaceMock();

        $accessMask = 15;
        $attribute = 'attribute';
        $subject = 'subject';

        $ldapVoter = new LdapVoter($accessProvider, $permissionProvider);
        $accessProvider->expects($this->once())
            ->method('getResourceAccessMask')
            ->with($subject, $token)
            ->willReturn($accessMask);
        $permissionProvider->expects($this->once())
            ->method('isGranted')
            ->with($attribute, $accessMask)
            ->willReturn(false);

        $this->assertEquals(Voter::ACCESS_DENIED, $ldapVoter->vote($token, $subject, [$attribute]));
    }

    public function testVoteOnAttributeAbstain()
    {
        $accessProvider = $this->getAccessProviderMock();
        $permissionProvider = $this->getPermissionProviderMock();
        $token = $this->getTokenInterfaceMock();

        $subject = 'subject';

        $ldapVoter = new LdapVoter($accessProvider, $permissionProvider);
        $accessProvider->expects($this->never())
            ->method('getResourceAccessMask');
        $permissionProvider->expects($this->never())
            ->method('isGranted');

        $this->assertEquals(Voter::ACCESS_ABSTAIN, $ldapVoter->vote($token, $subject, []));
    }

    /**
     * @return AccessProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAccessProviderMock()
    {
        $mock = $this->getMockBuilder(AccessProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getResourceAccessMask',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return PermissionProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPermissionProviderMock()
    {
        $mock = $this->getMockBuilder(PermissionProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'isGranted',
                ]
            )
            ->getMock();

        return $mock;
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
}

