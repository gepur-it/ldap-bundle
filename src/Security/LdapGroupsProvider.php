<?php
/**
 * Created by PhpStorm.
 * User: Andrii Yakovlev
 * Date: 08.12.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Ldap\LdapConnection;

/**
 * Class LdapGroupsProvider
 * @package LdapBundle\Security
 */
class LdapGroupsProvider
{
    private LdapConnection $ldapConnection;
    private EntityManagerInterface $entityManager;

    /**
     * LdapGroupsProvider constructor.
     *
     * @param LdapConnection $ldapConnection
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        LdapConnection $ldapConnection,
        EntityManagerInterface $entityManager
    )
    {
        $this->ldapConnection = $ldapConnection;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string[]
     */
    public function loadRemoteGroups(): array
    {
        $query = '(objectCategory=group)';
        $search = $this->ldapConnection->search($query);
        $entries = $search->execute();
        $groups = [];
        foreach ($entries as $entry) {
            if (!$entry->hasAttribute('cn')) {
                continue;
            }
            $name = $entry->getAttribute('cn')[0];
            $groups[] = $name;
        }

        return $groups;
    }

    /**
     * @param string $group
     */
    public function rememberGroup(string $group)
    {
        $this->entityManager->persist((new LdapRole($group)));
        $this->entityManager->flush();
    }

    /**
     * @param string $group
     */
    public function forgetGroup(string $group)
    {
        $groupRepository = $this->entityManager->getRepository(LdapRole::class);
        $group = $groupRepository->findOneByRole($group);
        if (null === $group) {
            return;
        }
        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }

    /**
     * @return array|LdapRole[]
     */
    public function getLocalGroups(): iterable
    {
        $repository = $this->entityManager->getRepository(LdapRole::class);

        return $repository->findAll();
    }
}
