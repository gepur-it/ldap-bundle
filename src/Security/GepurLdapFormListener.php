<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 27.11.17
 */

namespace GepurIt\LdapBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\Security\Http\ParameterBagUtils;

/**
 * Class CustomFormListener
 * @package LdapBundle\Security
 * @codeCoverageIgnore
 */
class GepurLdapFormListener extends UsernamePasswordFormAuthenticationListener
{
    /** @var  CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        if (null !== $this->csrfTokenManager) {
            $csrfToken = ParameterBagUtils::getRequestParameterValue($request, $this->options['csrf_parameter']);

            if (false === $this->csrfTokenManager->isTokenValid(
                    new CsrfToken($this->options['csrf_token_id'], $csrfToken)
                )) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
        }

        if ($request->getRequestFormat() === 'json') {
            $data = json_decode($request->getContent(), true);
            $username = $data[$this->options['username_parameter']] ?? null;
            $password = $data[$this->options['password_parameter']] ?? null;
        } elseif ($this->options['post_only']) {
            $username = trim(
                ParameterBagUtils::getParameterBagValue($request->request, $this->options['username_parameter'])
            );
            $password = ParameterBagUtils::getParameterBagValue(
                $request->request,
                $this->options['password_parameter']
            );
        } else {
            $username = trim(
                ParameterBagUtils::getRequestParameterValue($request, $this->options['username_parameter'])
            );
            $password = ParameterBagUtils::getRequestParameterValue($request, $this->options['password_parameter']);
        }

        if (strlen($username) > Security::MAX_USERNAME_LENGTH) {
            throw new BadCredentialsException('Invalid username.');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return $this->authenticationManager->authenticate(
            new UsernamePasswordToken($username, $password, $this->providerKey)
        );
    }
}

