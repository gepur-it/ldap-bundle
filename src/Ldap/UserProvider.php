<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Ldap;

use GepurIt\LdapBundle\Contracts\ErpUserProviderInterface;
use GepurIt\LdapBundle\Entry\EntryHelper;
use GepurIt\LdapBundle\Security\UserProfileProvider;
use GepurIt\User\Entity\UserProfile;
use GepurIt\User\Security\User;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 * @package GepurIt\LdapBundle\Ldap
 */
class UserProvider implements UserProviderInterface, ErpUserProviderInterface
{
    const DEFAULT_UID_KEY = 'sAMAccountName';
    const DEFAULT_SEARCH = '(sAMAccountName={username})';

    /** @var LdapConnection */
    private $ldapConnection;

    /** @var EntryHelper */
    private $entryHelper;

    /** @var UserProfileProvider */
    private $userProfileProvider;

    /** @var string */
    private $groupName;

    /**
     * @param LdapConnection $ldapConnection
     * @param EntryHelper $entryHelper
     * @param UserProfileProvider $userProfileProvider
     * @param string $groupName
     */
    public function __construct(
        LdapConnection $ldapConnection,
        EntryHelper $entryHelper,
        UserProfileProvider $userProfileProvider,
        string $groupName
    )
    {
        $this->ldapConnection = $ldapConnection;
        $this->entryHelper = $entryHelper;
        $this->userProfileProvider = $userProfileProvider;
        $this->groupName = $groupName;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        try {
            $username = $this->ldapConnection->escape($username, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{username}', $username, self::DEFAULT_SEARCH);
            $search = $this->ldapConnection->query($query);
            $entries = $search->execute();
        } catch (ConnectionException $exception) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $exception);
        }

        if ($entries->count() !== 1) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $user = $this->entryHelper->convertToUser($entries[0]);
        $user->setProfile($this->userProfileProvider->getProfile($user));

        return $user;
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function checkCredentials($username, $password): bool
    {
        try {
            $username = $this->ldapConnection->escape($username, '', LdapInterface::ESCAPE_DN);
            $query = str_replace('{username}', $username, self::DEFAULT_SEARCH);
            $result = $this->ldapConnection->query($query)->execute();
            if (1 !== $result->count()) {
                throw new BadCredentialsException('The presented username is invalid.');
            }
            $entityDN = $result[0]->getDn();
            $this->ldapConnection->bind($entityDN, $password);
        } catch (ConnectionException $exception) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $exception);
        }

        return true;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     * @return UserInterface
     *
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user = new User($user->getLogin(), $user->getUserId(), $user->getName(), $user->getRoles());
        $user->setProfile($this->userProfileProvider->getProfile($user));

        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === User::class;
    }

    /**
     * @param string $sid
     * @return User
     */
    public function loadUserBySid(string $sid): User
    {
        try {
            $sid = $this->ldapConnection->escape($sid, '', LdapInterface::ESCAPE_FILTER);
            $query = '(objectSid=' . $sid . ')';
            $search = $this->ldapConnection->query($query);
            $entries = $search->execute();
        } catch (ConnectionException $exception) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $sid), 0, $exception);
        }

        if ($entries->count() !== 1) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $sid));
        }

        $user = $this->entryHelper->convertToUser($entries[0]);
        $user->setProfile($this->userProfileProvider->getProfile($user));

        return $user;
    }

    /**
     * @param string $ldapSip
     * @return UserProfile|null
     */
    public function loadUserProfileBySid(string $ldapSip): ?UserProfile
    {
        $profile = $this->userProfileProvider->loadProfileById($ldapSip);
        if (null === $profile) {
            try {
                $user = $this->loadUserBySid($ldapSip);
            } catch (UsernameNotFoundException $exception) {
                return null;
            }
            $profile = $user->getProfile();
        }
        return $profile;
    }

    /**
     * In query - using huck from @see http://www.cmsmagazine.ru/library/items/cms/sc_in_lamp/
     * UserAccountControl: Check if Entry's attribute 'userAccountControl' has flag 'ACCOUNTDISABLE'
     * @see https://support.microsoft.com/ru-ru/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
     *
     * @return User[]
     */
    public function getActiveUsers(): array
    {
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $search = $this->ldapConnection->query($query);
        $entries = $search->execute();
        // we should filter users and use only members of defined group
        // this is fucking hardcode, the system administrators are to blame for everything
        $groupName = 'OU=' . $this->groupName;
        $entries = array_filter(
            $entries->toArray(),
            function ($entry) use ($groupName) {
                /** @var Entry $entry */
                if (!$entry->hasAttribute('memberOf') || !$entry->hasAttribute(self::DEFAULT_UID_KEY)) {
                    return false;
                }
                foreach ($entry->getAttribute('memberOf') as $group) {
                    if (false !== stripos($group, $groupName)) {
                        return true;
                    }
                }

                return false;
            }
        );

        $result = [];
        /** @var Entry[] $entries */
        foreach ($entries as $entry) {
            $result[] = $this->entryHelper->convertToUser($entry);
        }

        return $result;
    }

}