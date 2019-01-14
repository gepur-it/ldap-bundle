<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 10:45
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\LogoutSuccessHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class LogoutSuccessHandlerTest
 * @package LdapBundle\Security
 */
class LogoutSuccessHandlerTest extends TestCase
{
    public function testOnLogoutSuccessJson()
    {
        /** @var HttpUtils|\PHPUnit_Framework_MockObject_MockObject $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        $handler = new LogoutSuccessHandler($httpUtils, '/');

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('json');

        $response = $handler->onLogoutSuccess($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testOnLogoutSuccessHtml()
    {
        /** @var HttpUtils|\PHPUnit_Framework_MockObject_MockObject $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        $handler = new LogoutSuccessHandler($httpUtils, '/');

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('html');

        $response = $handler->onLogoutSuccess($request);

        $this->assertEquals(null, $response);
    }
}
