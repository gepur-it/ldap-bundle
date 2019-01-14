<?php

namespace GepurIt\LdapBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * LdapRoleAccessRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LdapRoleAccessRepository extends EntityRepository
{
    /**
     * @param TokenInterface $token
     * @return LdapRoleAccess[]
     */
    public function findByToken(TokenInterface $token): array
    {
        $rolesAsString = array_map(
            function (Role $role) {
                return $role->getRole();
            },
            $token->getRoles()
        );

        $query = $this->createQueryBuilder('ldapRoleAccess')
            ->addSelect('resource')
            ->addSelect('role')
            ->join('ldapRoleAccess.resource','resource')
            ->join('ldapRoleAccess.role','role')
            ->where('role.role IN (:roles)')->setParameter('roles', $rolesAsString)
            ->getQuery();

        /** @var LdapRoleAccess[] $ldapRolesAccess */
        $ldapRolesAccess = $query->getResult();

        return $ldapRolesAccess;
    }
}
