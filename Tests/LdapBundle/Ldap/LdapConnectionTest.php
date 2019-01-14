<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 13.11.17
 */

namespace GepurIt\LdapBundle\Tests\Ldap;

use GepurIt\LdapBundle\Ldap\LdapConnection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\LdapInterface;

/**
 * Class LdapConnectionTest
 * @package LdapBundle\Ldap
 */
class LdapConnectionTest extends TestCase
{
    public function testSearch()
    {
        $ldap = $this->getLdapInterfaceMock();
        $baseDn = 'baseDn';
        $searchQuery = 'searchQuery';
        $searchUser = 'searchUser';
        $password = 'password';
        $connection = new LdapConnection($ldap, $baseDn, $searchQuery, $searchUser, $password);

        $queryInterface = $this->getQueryInterfaceMock();
        $query = 'query';
        $ldap->expects($this->once())
            ->method('bind')
            ->with($searchUser, $password);

        $ldap->expects($this->once())
            ->method('query')
            ->with($searchQuery, $query)
            ->willReturn($queryInterface);

        $this->assertSame($queryInterface, $connection->search($query));
    }

    public function testQuery()
    {
        $ldap = $this->getLdapInterfaceMock();
        $baseDn = 'baseDn';
        $searchQuery = 'searchQuery';
        $searchUser = 'searchUser';
        $password = 'password';
        $connection = new LdapConnection($ldap, $baseDn, $searchQuery, $searchUser, $password);

        $queryInterface = $this->getQueryInterfaceMock();
        $query = 'query';
        $ldap->expects($this->once())
            ->method('bind')
            ->with($searchUser, $password);

        $ldap->expects($this->once())
            ->method('query')
            ->with($baseDn, $query)
            ->willReturn($queryInterface);

        $this->assertSame($queryInterface, $connection->query($query));
    }

    public function testEscape()
    {
        $ldap = $this->getLdapInterfaceMock();
        $baseDn = 'baseDn';
        $searchQuery = 'searchQuery';
        $searchUser = 'searchUser';
        $password = 'password';
        $connection = new LdapConnection($ldap, $baseDn, $searchQuery, $searchUser, $password);

        $subject = 'subject';
        $ignore = 'ignore';
        $flags = 0;
        $result = 'result';
        $ldap->expects($this->once())
            ->method('escape')
            ->with($subject, $ignore, $flags)
            ->willReturn($result);

        $this->assertEquals($result, $connection->escape($subject, $ignore, $flags));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LdapInterface
     */
    private function getLdapInterfaceMock()
    {
        $mock = $this->getMockBuilder(LdapInterface::class)
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
}

