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
 * LdapResource
 *
 * @ORM\Table(name="ldap_resource")
 * @ORM\Entity(repositoryClass="GepurIt\LdapBundle\Repository\LdapResourceRepository")
 * @codeCoverageIgnore
 */
class LdapResource
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
     * @ORM\Column(name="resource", type="string", length=255, unique=true)
     */
    private string $resource = '';

    /**
     * @var ArrayCollection|LdapRoleAccess[]
     * @ORM\OneToMany(targetEntity="GepurIt\LdapBundle\Entity\LdapRoleAccess", mappedBy="resource")
     */
    private $roleAccesses;

    /**
     * LdapResource constructor.
     */
    public function __construct()
    {
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
     * Get resource
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return LdapResource
     */
    public function setResource(string $resource): LdapResource
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return ArrayCollection|LdapRoleAccess[]
     */
    public function getRoleAccesses()
    {
        return $this->roleAccesses;
    }

    /**
     * @param ArrayCollection|LdapRoleAccess[] $roleAccesses
     */
    public function setRoleAccesses($roleAccesses)
    {
        $this->roleAccesses = $roleAccesses;
    }
}

