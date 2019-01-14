<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */

namespace GepurIt\LdapBundle\Ldap;

use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\LdapInterface;

/**
 * This is an "facade" for Ldap
 * Class LdapConnection
 * @package LdapBundle\Ldap
 */
class LdapConnection
{
    /** @var LdapInterface */
    private $ldap;

    /** @var string */
    private $baseDn;

    /** @var string */
    private $searchQuery;

    /** @var string */
    private $searchUser;

    /** @var string */
    private $password;

    /**
     * LdapConnection constructor.
     *
     * @param LdapInterface $ldap
     * @param string        $baseDn
     * @param string        $searchQuery
     * @param string        $searchUser
     * @param string        $password
     */
    public function __construct(
        LdapInterface $ldap,
        string $baseDn,
        string $searchQuery,
        string $searchUser,
        string $password
    ) {
        $this->ldap        = $ldap;
        $this->baseDn      = $baseDn;
        $this->searchQuery = $searchQuery;
        $this->searchUser  = $searchUser;
        $this->password    = $password;
    }

    /**
     * @param string $query
     *
     * @return \Symfony\Component\Ldap\Adapter\QueryInterface
     */
    public function search(string $query): QueryInterface
    {
        $ldap = $this->ldap;
        $ldap->bind($this->searchUser, $this->password);

        return $ldap->query($this->searchQuery, $query);
    }

    /**
     * @param string $query
     *
     * @return \Symfony\Component\Ldap\Adapter\QueryInterface
     */
    public function query(string $query): QueryInterface
    {
        $ldap = $this->ldap;
        $ldap->bind($this->searchUser, $this->password);

        return $ldap->query($this->baseDn, $query);
    }

    /**
     * @param string $subject
     * @param string $ignore
     * @param int    $flags
     *
     * @return string
     */
    public function escape($subject, $ignore, $flags)
    {
        $ldap = $this->ldap;

        return $ldap->escape($subject, $ignore, $flags);
    }
}
