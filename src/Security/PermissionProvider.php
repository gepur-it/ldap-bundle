<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 08.12.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Security;

/**
 * Class PermissionProvider
 * @package LdapBundle\Security
 */
class PermissionProvider
{
    const MASK__DENY_ALL = 0b00000000;
    const MASK__READ = 0b00000001;
    const MASK__WRITE = 0b00000010;
    const MASK__APPROVE = 0b00000100;
    const MASK__DELETE = 0b00001000;

    private array $maskMap = [
        'READ' => self::MASK__READ,
        'WRITE' => self::MASK__WRITE,
        'APPROVE' => self::MASK__APPROVE,
        'DELETE' => self::MASK__DELETE,
    ];

    /**
     * @param string $permissionName
     * @param int $permission
     *
     * @return bool
     */
    public function isGranted(string $permissionName, int $permission): bool
    {
        return (bool)($this->getPermissionMask($permissionName) & $permission);
    }

    /**
     * @param string $permissionName
     *
     * @return int|mixed
     */
    public function getPermissionMask(string $permissionName)
    {
        return $this->maskMap[$permissionName] ?? self::MASK__DENY_ALL;
    }
}
