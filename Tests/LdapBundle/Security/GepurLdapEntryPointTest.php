<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 12:03
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\GepurLdapEntryPoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class CustomEntryPointTest
 * @package LdapBundle\Security
 */
class GepurLdapEntryPointTest extends TestCase
{
    /**
     *
     */
    public function testStartJson()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var AuthenticationException|\PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->createMock(AuthenticationException::class);
        $point = new GepurLdapEntryPoint();

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('json');

        $response = $point->start($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStartHtml()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var AuthenticationException|\PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->createMock(AuthenticationException::class);
        $point = new GepurLdapEntryPoint();

        $request->expects($this->once())
            ->method('getRequestFormat')->willReturn('html');

        $response = $point->start($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
    }
}
