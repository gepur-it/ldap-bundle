<?php

namespace GepurIt\LdapBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * LdapResource
 *
 * @ORM\Table(name="ldap_resource")
 * @ORM\Entity(repositoryClass="GepurIt\LdapBundle\Repository\LdapResourceRepository")
 * @JMS\ExclusionPolicy("all")
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
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="resource", type="string", length=255, unique=true)
     */
    private $resource;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return LdapResource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
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

