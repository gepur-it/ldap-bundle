<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 05.03.18
 * Time: 17:26
 */

namespace GepurIt\LdapBundle\Entry;

use GepurIt\User\Security\User;

/**
 * Class DummyUser
 * @package LdapBundle\Entry
 */
class DummyUser extends User
{
    const DEFAULT_ID = 'dummy';

    /**
     * DummyUser constructor.
     */
    public function __construct()
    {
        $login   = self::DEFAULT_ID;
        $ldapSid = self::DEFAULT_ID;
        $name    = self::DEFAULT_ID;
        $roles   = [];
        parent::__construct($login, $ldapSid, $name, $roles);
    }
}
