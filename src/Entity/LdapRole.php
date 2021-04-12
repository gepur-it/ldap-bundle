<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);


namespace GepurIt\LdapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * LdapRole
 *
 * @ORM\Table(name="ldap_role")
 * @ORM\Entity(repositoryClass="GepurIt\LdapBundle\Repository\LdapRoleRepository")
 * @codeCoverageIgnore
 */
class LdapRole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, unique=true)
     */
    private string $role = '';

    /**
     * @var ArrayCollection|LdapRoleAccess[]
     * @ORM\OneToMany(targetEntity="GepurIt\LdapBundle\Entity\LdapRoleAccess", mappedBy="role", cascade={"remove"})
     */
    private $roleAccesses;

    /**
     * LdapRole constructor.
     *
     * @param string $role
     */
    public function __construct(string $role)
    {
        $this->role         = $role;
        $this->roleAccesses = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return ArrayCollection|LdapRoleAccess[]
     */
    public function getRoleAccesses()
    {
        return $this->roleAccesses;
    }
}

