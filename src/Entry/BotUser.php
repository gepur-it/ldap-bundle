<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 16.03.18
 * Time: 11:25
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Entry;

use GepurIt\User\Security\User;

/**
 * Class BotUser
 * @package LdapBundle\Entry
 */
class BotUser extends User
{
    /**
     * DummyUser constructor.
     */
    public function __construct()
    {
        $login   = 'gepur';
        $ldapSid = 'S-1-5-21-821191414-507608688-2850428263-1326';
        $name    = 'Gepur помошник';
        $roles   = [];
        parent::__construct($login, $ldapSid, $name, $roles);
    }
}
