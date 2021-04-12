<?php
/**
 * Created by PhpStorm.
 * User: Andrii Yakovlev
 * Date: 08.12.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;
use GepurIt\LdapBundle\Repository\LdapRoleAccessRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AccessProvider
 * @package LdapBundle\Security
 */
class AccessProvider
{
    private array $accessorsMap = [];
    private EntityManagerInterface $entityManager;

    /**
     * AccessProvider constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $resource
     * @param TokenInterface $token
     * @return int
     */
    public function getResourceAccessMask(string $resource, TokenInterface $token): int
    {
        $userName = $token->getUsername();
        if (empty($this->accessorsMap[$userName])) {
            $this->accessorsMap[$userName] = $this->loadAccessorMap($token);
        }

        return $this->accessorsMap[$userName][$resource] ?? 0;
    }

    /**
     * @param TokenInterface $token
     *
     * @return array
     */
    protected function loadAccessorMap(TokenInterface $token): array
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
