<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 11:41
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\FailureHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class FailureHandlerTest
 * @package LdapBundle\Security
 */
class FailureHandlerTest extends TestCase
{
    public function testOnAuthenticationFailureJson()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var AuthenticationException|\PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->createMock(AuthenticationException::class);
        /** @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject $httpKernel */
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        /** @var HttpUtils|\PHPUnit_Framework_MockObject_MockObject $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);

        $handler = new FailureHandler($httpKernel, $httpUtils, [], null);

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('json');

        $response = $handler->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(Response::class, $response);

    }

    public function testOnAuthenticationFailureHtml()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var AuthenticationException|\PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->createMock(AuthenticationException::class);
        /** @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject $httpKernel */
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        /** @var HttpUtils|\PHPUnit_Framework_MockObject_MockObject $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);

        $handler = new FailureHandler($httpKernel, $httpUtils, [], null);

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('html');

        $response = $handler->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
    }
}
