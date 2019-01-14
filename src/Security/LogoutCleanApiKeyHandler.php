<?php
/**
 * Created by PhpStorm.
 * User: yawa
 * Date: 15.12.17
 * Time: 11:34
 */

namespace GepurIt\LdapBundle\Security;


use Doctrine\ODM\MongoDB\DocumentManager;
use GepurIt\LdapBundle\Document\UserApiKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutCleanApiKeyHandler  implements LogoutHandlerInterface
{
    /**
     * @var DocumentManager
     */
    private $documentManager;
    /**
     * LogoutCleanApiKeyHandler constructor.
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $username = $token->getUsername();
        $this->documentManager->createQueryBuilder(UserApiKey::class)
            ->remove()
            ->field('username')->equals($username)
            ->getQuery()
            ->execute();
    }
}
