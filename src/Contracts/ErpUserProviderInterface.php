<?php
/**
 * Gepur ERP.
 * Author: Andrii Yakovlev <yawa20@gmail.com>
 * Date: 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Contracts;

use GepurIt\User\Entity\UserProfile;
use GepurIt\User\Security\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface ErpUserProviderInterface
 * @package GepurIt\LdapBundle\Contracts
 */
interface ErpUserProviderInterface extends UserProviderInterface
{
    /**
     * @param string $sid
     * @return User
     */
    public function loadUserBySid(string $sid): User;

    /**
     * @return array
     */
    public function getActiveUsers(): array;

    /**
     * @param string $ldapSip
     * @return UserProfile|null
     */
    public function loadUserProfileBySid(string $ldapSip): ?UserProfile;

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function checkCredentials($username, $password): bool;
}