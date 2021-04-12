<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

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
    private LdapInterface $ldap;
    private string $baseDn;
    private string $searchQuery;
    private string $searchUser;
    private string $password;

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
     * @return QueryInterface
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
     * @return QueryInterface
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
     * @param int $flags
     *
     * @return string
     */
    public function escape(string $subject, string $ignore, int $flags): string
    {
        $ldap = $this->ldap;

        return $ldap->escape($subject, $ignore, $flags);
    }

    /**
     * @param $dn
     * @param $password
     * @return mixed
     */
    public function bind($dn, $password)
    {
        return $this->ldap->bind($dn, $password);
    }
}
