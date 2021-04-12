<?php
/**
 * Gepur ERP.
 * Author: Andrii Yakovlev <yawa20@gmail.com>
 * Date: 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\ApiKey;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use GepurIt\LdapBundle\Document\UserApiKey;

/**
 * Class ApiKeyStorage
 * @package GepurIt\LdapBundle\ApiKey
 */
class ApiKeyStorage
{
    private DocumentManager $documentManager;

    /**
     * ApiKeyStorage constructor.
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @param $apiKey
     * @return UserApiKey|null
     */
    public function findUserApiKey(string $apiKey): ?UserApiKey
    {
        /** @var UserApiKey|null $userApiKey */
        $userApiKey = $this->documentManager->find(UserApiKey::class, $apiKey);

        return $userApiKey;
    }

    /**
     * @param string $apiKey
     * @return bool
     * @throws MongoDBException
     */
    public function reloadCredentials(string $apiKey): bool
    {
        $userApiKey = $this->findUserApiKey($apiKey);
        if (null === $userApiKey) {
            return false;
        }

        if (time() - $userApiKey->getLastActivity()->getTimestamp() > UserApiKey::EXPIRATION_TIME) {
            return false;
        }
        $userApiKey->updateLastActivity();
        $this->documentManager->persist($userApiKey);
        $this->documentManager->flush();

        return true;
    }

    /**
     * @param UserApiKey $userApiKey
     * @throws MongoDBException
     */
    public function prolongApiKey(UserApiKey $userApiKey)
    {
        $userApiKey->updateLastActivity();
        $this->documentManager->persist($userApiKey);
        $this->documentManager->flush();
    }
}