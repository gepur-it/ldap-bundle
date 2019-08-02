<?php
/**
 * Created by PhpStorm.
 * User: Andrii Yakovlev
 * Date: 08.12.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class LdapVoter
 * @package LdapBundle\Security
 */
class LdapVoter extends Voter
{
    /**
     * @var AccessProvider
     */
    private $accessProvider;

    /**
     * @var PermissionProvider
     */
    private $permissionProvider;

    /**
     * LdapVoter constructor.
     * @param AccessProvider $accessProvider
     * @param PermissionProvider $permissionProvider
     */
    public function __construct(AccessProvider $accessProvider, PermissionProvider $permissionProvider)
    {
        $this->permissionProvider = $permissionProvider;
        $this->accessProvider = $accessProvider;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return (is_string($subject));
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $accessMask = $this->accessProvider->getResourceAccessMask($subject, $token);

        return $this->permissionProvider->isGranted($attribute, $accessMask);
    }
}
