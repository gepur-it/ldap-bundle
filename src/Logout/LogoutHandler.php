<?php
/**
 * Created by PhpStorm.
 * User: yawa
 * Date: 15.12.17
 * Time: 11:34
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Logout;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use GepurIt\LdapBundle\Document\UserApiKey;
use GepurIt\User\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Class LogoutCleanApiKeyHandler
 * @package GepurIt\LdapBundle\Security
 */
class LogoutHandler implements LogoutHandlerInterface
{
    private DocumentManager $documentManager;

    /**
     * LogoutCleanApiKeyHandler constructor.
     *
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
     *
     * @param Request        $request
     * @param Response       $response
     * @param TokenInterface $token
     *
     * @throws MongoDBException
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->documentManager->createQueryBuilder(UserApiKey::class)
            ->remove()
            ->field('userId')->equals($user->getUserId())
            ->getQuery()
            ->execute();
    }
}
