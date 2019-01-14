<?php

namespace GepurIt\LdapBundle\Security;

use Doctrine\ORM\EntityManager;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;
use GepurIt\LdapBundle\Repository\LdapRoleAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessProvider
 * @package LdapBundle\Security
 */
class AccessProvider
{
    /**
     * @var array
     */
    private $accessorsMap = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getResourceAccessMask(string $resource, TokenInterface $token)
    {
        $userName = $token->getUsername();
        if (empty($this->accessorsMap[$userName])) {
            $this->accessorsMap[$userName] = $this->loadAccessorMap($token);
        }

        return $this->accessorsMap[$userName][$resource] ?? 0;
    }

    /**
     * @param TokenInterface $token
     * @return array
     */
    protected function loadAccessorMap(TokenInterface $token)
    {
        /** @var LdapRoleAccessRepository $repository */
        $repository = $this->entityManager->getRepository(LdapRoleAccess::class);
        $roleAccesses = $repository->findByToken($token);
        $result = [];
        foreach ($roleAccesses as $roleAccess) {
            $resource = $roleAccess->getResource()->getResource();
            if (!isset($result[$resource])) {
                $result[$resource] = 0;
            }
            $result[$resource] = $result[$resource] | $roleAccess->getPermissionMask();
        }

        return $result;
    }


}
