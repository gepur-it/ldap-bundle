<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 28.09.17
 */

namespace GepurIt\LdapBundle\Security;

use Doctrine\ODM\MongoDB\DocumentManager;
use GepurIt\ActionLoggerBundle\Logger\ActionLoggerInterface;
use GepurIt\LdapBundle\Document\UserApiKey;
use GepurIt\User\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class LoginSuccessHandler
 * @package AppBundle\Security
 */
class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var ActionLoggerInterface */
    private $actionLogger;

    /** @var DocumentManager */
    private $documentManager;

    /**
     * LoginSuccessHandler constructor.
     *
     * @param ActionLoggerInterface $actionLogger
     * @param HttpUtils             $httpUtils
     * @param DocumentManager       $documentManager
     * @param array                 $options
     */
    public function __construct(
        ActionLoggerInterface $actionLogger,
        HttpUtils $httpUtils,
        DocumentManager $documentManager,
        array $options = []
    ) {
        $this->actionLogger    = $actionLogger;
        $this->documentManager = $documentManager;
        parent::__construct($httpUtils, $options);
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return Response never null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->actionLogger->log('login', 'User Login');
        /** @var User $user */
        $user = $token->getUser();

        // remove all exists api keys for this user
        $this->documentManager->createQueryBuilder(UserApiKey::class)
            ->remove()
            ->field('username')->equals($user->getUsername())
            ->getQuery()
            ->execute();

        // add new api key
        $userApiKey = new UserApiKey();
        $userApiKey->setUsername($user->getUsername());
        $userApiKey->setApiKey(uniqid().uniqid().uniqid());
        $userApiKey->setUserId($user->getUserId());
        $userApiKey->setObjectGUID($user->getObjectGUID());
        $this->documentManager->persist($userApiKey);
        $this->documentManager->flush();

        return JsonResponse::fromJsonString(json_encode($userApiKey), 200);

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }
}

