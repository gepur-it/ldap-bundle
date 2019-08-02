<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Entry;

use GepurIt\LdapBundle\Ldap\UserProvider;
use GepurIt\User\Security\User;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class EntryHelper
 * @package LdapBundle\Entry
 * @codeCoverageIgnore
 */
class EntryHelper
{
    const REGEX_GROUP_NAME = '/^CN=(?P<group>[^,]+).*$/i';
    const DEFAULT_ROLES    = ['ROLE_USER'];

    /**
     * @param Entry $entry
     *
     * @return User
     */
    public function convertToUser(Entry $entry)
    {
        $username       = $this->getAttributeValue($entry, UserProvider::DEFAULT_UID_KEY);
        $sid            = $this->extractSID($entry);
        $extractedRoles = $this->extractRoles($entry);
        $params         = $this->extractParams($entry);
        $roles          = array_merge(EntryHelper::DEFAULT_ROLES, $extractedRoles);
        $name           = $this->extractName($entry);
        $guid           = bin2hex($entry->getAttribute('objectGUID')[0]);

        $user = new User($username, $sid, $name, $roles); // Create and return the user object;
        foreach ($params as $name => $value) {
            $user->setParam($name, $value);
        }
        $user->setObjectGUID($guid);

        return $user;
    }

    /**
     * Fetches a required unique attribute value from an LDAP entry.
     *
     * @param null|Entry $entry
     * @param string     $attribute
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    private function getAttributeValue(Entry $entry, $attribute)
    {
        if (!$entry->hasAttribute($attribute)) {
            throw new InvalidArgumentException(
                sprintf('Missing attribute "%s" for user "%s".', $attribute, $entry->getDn())
            );
        }

        $values = $entry->getAttribute($attribute);

        if (1 !== count($values)) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" has multiple values.', $attribute));
        }

        return $values[0];
    }

    /**
     * @param Entry $entry
     *
     * @return string
     */
    private function extractName(Entry $entry): string
    {
        if (!$entry->hasAttribute('cn')) {
            return '';
        }

        return $entry->getAttribute('cn')[0];
    }

    /**
     * @param Entry $entry
     *
     * @return string
     */
    private function extractSID(Entry $entry): string
    {
        if (!$entry->hasAttribute('objectSid')) {
            return '';
        }

        return $this->decodeSID($entry->getAttribute('objectSid')[0]);
    }

    /**
     * @param Entry $entry
     *
     * @return array
     */
    private function extractRoles(Entry $entry)
    {
        $roles  = [];
        $groups = ($entry->hasAttribute('memberOf'))
            ? $entry->getAttribute('memberOf')
            : [];
        foreach ($groups as $groupLine) { // Iterate through each group entry line
            $groupName = strtolower(
                $this->getGroupName($groupLine)
            ); // Extract and normalize the group name from the line
            $roles[]   = $groupName; // Map the group to the role the user will have
        }

        return $roles;
    }

    /**
     * Decode the binary SID into its readable form.
     *
     * @param string $encodedSID
     *
     * @return string
     */
    private function decodeSID(string $encodedSID): ?string
    {
        $sid            = @unpack('C1rev/C1count/x2/N1id', $encodedSID);
        $subAuthorities = [];

        if (!isset($sid['id']) || !isset($sid['rev'])) {
            throw new \UnexpectedValueException(
                'The revision level or identifier authority was not found when decoding the SID.'
            );
        }

        $revisionLevel       = $sid['rev'];
        $identifierAuthority = $sid['id'];
        $subs                = isset($sid['count']) ? $sid['count'] : 0;

        // The sub-authorities depend on the count, so only get as many as the count, regardless of data beyond it
        for ($iterator = 0; $iterator < $subs; $iterator++) {
            $subAuthorities[] = unpack('V1sub', hex2bin(substr(bin2hex($encodedSID), 16 + ($iterator * 8), 8)))['sub'];
        }
        $subAuthorities = implode(preg_filter('/^/', '-', $subAuthorities));

        return 'S-'.$revisionLevel.'-'.$identifierAuthority.$subAuthorities;
    }

    /**
     * Get the group name from the DN
     *
     * @param string $activeDirectoryDn
     *
     * @return string
     */
    private function getGroupName($activeDirectoryDn): string
    {
        $matches = [];

        return preg_match(self::REGEX_GROUP_NAME, $activeDirectoryDn, $matches) ? $matches['group'] : '';
    }

    /**
     * @param Entry $entry
     *
     * @return array
     */
    private function extractParams(Entry $entry): array
    {
        $result = [];
        if (!$entry->hasAttribute('description')) {
            return [];
        }
        $description = trim($entry->getAttribute('description')[0]);
        if (empty($description)) {
            return [];
        }
        $records = explode(';', $description);
        foreach ($records as $record) {
            if (empty($record)) {
                continue;
            }
            $params = explode('=', $record);
            if (count($params) < 2) {
                continue;
            }

            $key          = trim($params[0]);
            $value        = $params[1];
            $result[$key] = $value;
        }

        return $result;
    }
}
