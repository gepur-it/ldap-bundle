<?php
/**
 * Gepur ERP.
 * Author: Andrii Yakovlev <yawa20@gmail.com>
 * Date: 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Guard\Token;

use GepurIt\LdapBundle\Document\UserApiKey;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

/**
 * Class ApiAuthToken
 * @package GepurIt\LdapBundle\Guard\Token
 */
class ApiAuthToken extends AbstractToken implements GuardTokenInterface
{
    private string $providerKey;
    private UserApiKey $apiKey;

    /**
     * ApiAuthToken constructor.
     * @param UserInterface $user
     * @param string $providerKey
     * @param array $roles
     * @param UserApiKey $apiKey
     */
    public function __construct(UserInterface $user, string $providerKey, array $roles, UserApiKey $apiKey)
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey (i.e. firewall key) must not be empty.');
        }

        $this->setUser($user);
        $this->providerKey = $providerKey;

        // this token is meant to be used after authentication success, so it is always authenticated
        // you could set it as non authenticated later if you need to
        $this->setAuthenticated(true);
        $this->apiKey = $apiKey;
    }

    /**
     * Returns the user credentials.
     *
     * @return array The user credentials
     */
    public function getCredentials(): array
    {
        return [];
    }

    /**
     * @return UserApiKey
     */
    public function getApiKey(): UserApiKey
    {
        return $this->apiKey;
    }
}