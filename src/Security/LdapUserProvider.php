<?php

namespace GepurIt\LdapBundle\Security;

use GepurIt\User\Entity\UserProfile;
use GepurIt\LdapBundle\Entry\EntryHelper;
use GepurIt\LdapBundle\Ldap\LdapConnection;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use GepurIt\User\Security\User;

/**
 * Handles the mapping of ldap groups to security roles.
 * Class LdapUserProvider
 * @package AppBundle\Security
 */
class LdapUserProvider implements UserProviderInterface
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
    ) {
        $this->ldapConnection = $ldapConnection;
        $this->entryHelper = $entryHelper;
        $this->userProfileProvider = $userProfileProvider;
        $this->groupName = $groupName;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === User::class;
    }

    /**
     * {@inheritdoc}
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
     * In query - using huck from @see http://www.cmsmagazine.ru/library/items/cms/sc_in_lamp/
     * UserAccountControl: Check if Entry's attribute 'userAccountControl' has flag 'ACCOUNTDISABLE'
     * @see https://support.microsoft.com/ru-ru/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-pro
     *
     * @return \GepurIt\User\Security\User[]
     */
    public function getActiveUsers()
    {
        $query = '(&(objectCategory=person)(!(UserAccountControl:1.2.840.113556.1.4.804:=2)))';
        $search = $this->ldapConnection->query($query);
        $entries = $search->execute();
        // we should filter users and use only members of defined group
        // this is fucking hardcode, the system administrators are to blame for everything
        $groupName = 'OU='.$this->groupName;
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

    /**
     * {@inheritdoc}
     * @param $username
     * @return User
     */
    public function loadUserByUsername($username)
    {
        try {
            $username = $this->ldapConnection->escape($username, '', LdapInterface::ESCAPE_FILTER);
            $query = str_replace('{username}', $username, self::DEFAULT_SEARCH);
            $search = $this->ldapConnection->query($query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        $entries = $search->execute();
        $count = count($entries);

        if (!$count) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($count > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }
        $user = $this->entryHelper->convertToUser($entries[0]);
        $user->setProfile($this->userProfileProvider->getProfile($user));

        return $user;
    }

    /**
     * @param string $sid
     * @return User
     */
    public function loadUserBySid(string $sid): User
    {
        try {
            $sid = $this->ldapConnection->escape($sid, '', LdapInterface::ESCAPE_FILTER);
            $query = '(objectSid='.$sid.')';
            $search = $this->ldapConnection->query($query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $sid), 0, $e);
        }

        $entries = $search->execute();
        $count = count($entries);

        if (!$count) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $sid));
        }

        if ($count > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }
        $user = $this->entryHelper->convertToUser($entries[0]);
        $user->setProfile($this->userProfileProvider->getProfile($user));

        return $user;
    }

    /**
     * @param string $ldapSip
     * @return UserProfile|null
     */
    public function loadUserProfileBySip(string $ldapSip): ?UserProfile
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
}
