<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 27.11.17
 */

namespace GepurIt\LdapBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

/**
 * Class FailureHandler
 * @package LdapBundle\Security
 */
class FailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->getRequestFormat() == 'json') {
            $response = new JsonResponse();
            $response->setStatusCode(401, 'Invalid credentials');

            return $response;
        }

        return new RedirectResponse('/login');
    }
}

