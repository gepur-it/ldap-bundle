<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 29.11.17
 */

namespace GepurIt\LdapBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Class UserApiKey
 * @package LdapBundle\Document
 * @MongoDB\Document
 * @MongoDB\HasLifecycleCallbacks()
 */
class UserApiKey implements \JsonSerializable
{
    /**
     * @var string
     * @MongoDB\Field(type="string")
     * @MongoDB\Id(strategy="NONE")
     */
    private $apiKey = '';

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $username = '';

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $userId = '';

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    private $objectGUID = '';

    /**
     * @var \DateTime
     * @MongoDB\Field(type="date")
     * @MongoDB\Index(expireAfterSeconds="3600")
     */
    private $lastActivity;

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity(): \DateTime
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity(\DateTime $lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @MongoDB\PrePersist()
     * @MongoDB\PreUpdate()
     */
    public function updateLastActivity()
    {
        $this->lastActivity = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getObjectGUID(): string
    {
        return $this->objectGUID;
    }

    /**
     * @param string $objectGUID
     */
    public function setObjectGUID(string $objectGUID): void
    {
        $this->objectGUID = $objectGUID;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "apiKey"   => $this->getApiKey(),
            "username" => $this->getUsername(),
            "userId"   => $this->getUserId(),
        ];
    }
}
