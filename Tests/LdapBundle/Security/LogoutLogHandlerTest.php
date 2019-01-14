<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 11:02
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\ActionLoggerBundle\Logger\ActionLoggerInterface;
use GepurIt\LdapBundle\Security\LogoutLogHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class LogoutLogHandlerTest
 * @package LdapBundle\Security
 */
class LogoutLogHandlerTest extends TestCase
{
    public function testLogout()
    {
        /** @var ActionLoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(ActionLoggerInterface::class);
        $handler = new LogoutLogHandler($logger);

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        /** @var Response|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->createMock(Response::class);

        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);

        $logger->expects($this->once())->method('log');

        $handler->logout($request, $response, $token);
    }
}
