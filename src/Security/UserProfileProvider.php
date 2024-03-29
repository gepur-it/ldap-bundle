<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 08.12.17
 * Time: 9:48
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\User\Entity\UserProfile;
use GepurIt\User\Security\User;

/**
 * Class UserProfileProvider
 * @package LdapBundle\Security
 */
class UserProfileProvider
{
    private EntityManagerInterface $entityManager;

    /**
     * UserProfileProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     *
     * @return UserProfile
     */
    public function getProfile(User $user): UserProfile
    {
        /** @var UserProfile $profile */
        $profile = $this->entityManager
            ->getRepository(UserProfile::class)
            ->find($sid = $user->getUserId());

        $name = $user->getName();

        if (null !== $profile) {
            return $profile;
        }

        $profile = new UserProfile();
        $profile->setManagerId($sid);
        $profile->setManagerSign(UserProfile::REGARDS_DEFAULT.$name);
        $profile->setManagerName($name);
        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    /**
     * @param string $userId
     *
     * @return UserProfile|null
     */
    public function loadProfileById(string $userId): ?UserProfile
    {
        /** @var UserProfile $profile */
        $profile = $this->entityManager
            ->getRepository(UserProfile::class)
            ->find($userId);

        return $profile;
    }
}
