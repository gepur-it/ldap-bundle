<?php
/**
 * Created by PhpStorm.
 * User: Andrii Yakovlev
 * Date: 08.12.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Repository\LdapResourceRepository;

/**
 * Class LdapResourcesProvider
 * @package LdapBundle\Security
 */
class LdapResourcesProvider
{
    private EntityManagerInterface $entityManager;

    /**
     * LdapGroupsProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $resourceName
     */
    public function createResource(string $resourceName)
    {
        $ldapResourceEntity = new LdapResource();
        $ldapResourceEntity->setResource($resourceName);
        $this->entityManager->persist($ldapResourceEntity);
        $this->entityManager->flush();
    }

    /**
     * @param string $resourceName
     */
    public function removeResource(string $resourceName)
    {
        $groupRepository = $this->entityManager->getRepository(LdapResource::class);
        $entity = $groupRepository->findOneByResource($resourceName);
        if (null === $entity) {
            return;
        }
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * @param string $resourceName
     *
     * @return bool
     */
    public function hasResource(string $resourceName): bool
    {
        /** @var LdapResourceRepository $resourceRepository */
        $resourceRepository = $this->entityManager->getRepository(LdapResource::class);

        return $resourceRepository->existsByResource($resourceName);
    }
}
