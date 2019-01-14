<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 28.09.17
 */

namespace GepurIt\LdapBundle\Security;

use GepurIt\ActionLoggerBundle\Logger\ActionLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Class LogoutSuccessHandler
 * @package AppBundle\Security
 */
class LogoutLogHandler implements LogoutHandlerInterface

{
    /** @var ActionLoggerInterface */
    private $actionLogger;

    /**
     * LogoutHandler constructor.
     * @param ActionLoggerInterface $actionLogger
     */
    public function __construct(ActionLoggerInterface $actionLogger)
    {
        $this->actionLogger = $actionLogger;
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->actionLogger->log('logout', 'User Logged Out');
    }
}

