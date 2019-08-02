<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LdapRoleAccess
 *
 * @ORM\Table(name="ldap_role_access")
 * @ORM\Entity(repositoryClass="GepurIt\LdapBundle\Repository\LdapRoleAccessRepository")
 * @codeCoverageIgnore
 */
class LdapRoleAccess
{
    /**
     * @var LdapRole
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="GepurIt\LdapBundle\Entity\LdapRole", inversedBy="roleAccesses")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;

    /**
     * @var LdapResource
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="GepurIt\LdapBundle\Entity\LdapResource", inversedBy="roleAccesses")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    private $resource;

    /**
     * @var int
     *
     * @ORM\Column(name="permission", type="integer", nullable=false)
     */
    private $permissionMask = 0;

    /**
     * LdapRoleAccess constructor.
     *
     * @param LdapRole     $role
     * @param LdapResource $resource
     */
    public function __construct(LdapRole $role, LdapResource $resource)
    {
        $this->role     = $role;
        $this->resource = $resource;
    }

    /**
     * Get role
     *
     * @return LdapRole
     */
    public function getRole(): LdapRole
    {
        return $this->role;
    }

    /**
     * Get resource
     *
     * @return LdapResource
     */
    public function getResource(): LdapResource
    {
        return $this->resource;
    }

    /**
     * Get permission - bitmask permission
     *
     * @return int
     */
    public function getPermissionMask(): int
    {
        return $this->permissionMask;
    }

    /**
     * Set permission
     *
     * @param int $permissionMask
     */
    public function setPermissionMask(int $permissionMask)
    {
        $this->permissionMask = $permissionMask;
    }
}

