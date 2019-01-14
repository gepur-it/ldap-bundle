<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 29.11.17
 */

namespace GepurIt\LdapBundle\Security;

use Doctrine\ODM\MongoDB\DocumentManager;
use GepurIt\User\Security\User;
use GepurIt\LdapBundle\Document\UserApiKey;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class ApiKeyUserProvider
 * @package LdapBundle\Security
 */
class ApiKeyUserProvider implements UserProviderInterface
{
    /** @var LdapUserProvider */
    private $userProvider;
    /** @var DocumentManager  */
    private $documentManager;

    /**
     * ApiKeyUserProvider constructor.
     * @param LdapUserProvider $userProvider
     */
    public function __construct(LdapUserProvider $userProvider, DocumentManager $documentManager)
    {
        $this->userProvider = $userProvider;
        $this->documentManager = $documentManager;
    }

    /**
     * @param $apiKey
     * @return null|string
     */
    public function getUsernameForApiKey($apiKey)
    {
        /** @var UserApiKey|null $userApiKey */
        $userApiKey = $this->documentManager->getRepository(UserApiKey::class)
            ->find($apiKey);

        if (null === $userApiKey) {
            return null;
        }

        $userApiKey->updateLastActivity();
        $this->documentManager->persist($userApiKey);
        $this->documentManager->flush($userApiKey);

        return $userApiKey->getUserName();
    }

    /**
     * @param string $username
     * @return User|UserInterface
     */
    public function loadUserByUsername($username)
    {
        return $this->userProvider->loadUserByUsername($username);
    }

    /**
     * @param UserInterface $user
     * @return void
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
