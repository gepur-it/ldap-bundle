<?php
/**
 * Gepur ERP.
 * Author: Andrii Yakovlev <yawa20@gmail.com>
 * Date: 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Guard;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use GepurIt\ActionLoggerBundle\Logger\ActionLoggerInterface;
use GepurIt\LdapBundle\Contracts\ErpUserProviderInterface;
use GepurIt\LdapBundle\Document\UserApiKey;
use GepurIt\User\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class LdapAuthenticator
 * @package GepurIt\LdapBundle\Guard
 */
class LdapAuthenticator implements AuthenticatorInterface
{
    private ErpUserProviderInterface $userProvider;
    private ActionLoggerInterface $actionLogger;
    private DocumentManager $documentManager;

    /**
     * LdapAuthenticator constructor.
     * @param ErpUserProviderInterface $userProvider
     * @param ActionLoggerInterface $actionLogger
     * @param DocumentManager $documentManager
     */
    public function __construct(
        ErpUserProviderInterface $userProvider,
        ActionLoggerInterface $actionLogger,
        DocumentManager $documentManager
    )
    {
        $this->userProvider = $userProvider;
        $this->actionLogger = $actionLogger;
        $this->documentManager = $documentManager;
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * This is called when an anonymous request accesses a resource that
     * requires authentication. The job of this method is to return some
     * response that "helps" the user start into the authentication process.
     *
     * Examples:
     *
     * - For a form login, you might redirect to the login page
     *
     *     return new RedirectResponse('/login');
     *
     * - For an API token authentication system, you return a 401 response
     *
     *     return new Response('Auth header required', 401);
     *
     * @param Request $request The request that resulted in an AuthenticationException
     * @param AuthenticationException|null $authException The exception that started the authentication process
     *
     * @return Response
     */
    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        if ($request->getRequestFormat() == 'json') {
            return new JsonResponse('Authorisation required', 401);
        }
        return new Response('Authorisation required', 401);
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return ((null !== $request->get('_username')) && (null !== $request->get('_password')));
    }

    /**
     * Get the authentication credentials from the request and return them
     * as any type (e.g. an associate array).
     *
     * Whatever value you return here will be passed to getUser() and checkCredentials()
     *
     * For example, for a form login, you might:
     *
     *      return [
     *          'username' => $request->request->get('_username'),
     *          'password' => $request->request->get('_password'),
     *      ];
     *
     * Or for an API token that's on a header, you might use:
     *
     *      return ['api_key' => $request->headers->get('X-API-TOKEN')];
     *
     * @param Request $request
     * @return array Any non-null value
     *
     */
    public function getCredentials(Request $request): array
    {
        return [
            'username' => $request->get('_username', ''),
            'password' => $request->get('_password', ''),
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     * @throws AuthenticationException
     *
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param mixed $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        list('username' => $username, 'password' => $password) = $credentials;
        if ('' === (string)$password) {
            throw new BadCredentialsException('The presented password must not be empty.');
        }

        return $this->userProvider->checkCredentials($username, $password);
    }

    /**
     * Create an authenticated token for the given user.
     *
     * If you don't care about which token class is used or don't really
     * understand what a "token" is, you can skip this method by extending
     * the AbstractGuardAuthenticator class from your authenticator.
     *
     * @param UserInterface $user
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return GuardTokenInterface
     * @see AbstractGuardAuthenticator
     *
     */
    public function createAuthenticatedToken(UserInterface $user, string $providerKey)
    {
        return new PostAuthenticationGuardToken(
            $user,
            $providerKey,
            $user->getRoles()
        );
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([$exception->getMessageKey(), $exception->getMessageData()], 401);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey The provider (i.e. firewall) key
     *
     * @return Response|null
     * @throws \JsonException|MongoDBException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        $this->actionLogger->log('login', 'User Login');
        /** @var User $user */
        $user = $token->getUser();

        // add new api key
        $userApiKey = new UserApiKey();
        $userApiKey->setUsername($user->getUsername());
        $userApiKey->setApiKey(uniqid() . uniqid() . uniqid());
        $userApiKey->setUserId($user->getUserId());
        $userApiKey->setObjectGUID($user->getObjectGUID());
        $this->documentManager->persist($userApiKey);
        $this->documentManager->flush();

        return JsonResponse::fromJsonString(json_encode($userApiKey, JSON_THROW_ON_ERROR), 200);
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}