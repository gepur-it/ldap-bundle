<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 12:09
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\ApiKeyAuthenticator;
use GepurIt\LdapBundle\Security\ApiKeyUserProvider;
use GepurIt\User\Security\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class ApiKeyAuthenticatorTest
 * @package LdapBundle\Security
 */
class ApiKeyAuthenticatorTest extends TestCase
{

    public function testAuthenticateTokenWrongProvider()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        /** @var UserProviderInterface|\PHPUnit_Framework_MockObject_MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);

        $authenticator = new ApiKeyAuthenticator();
        $this->expectException(\InvalidArgumentException::class);
        $authenticator->authenticateToken($token, $userProvider, 'api');
        $this->assertInstanceOf(PreAuthenticatedToken::class, $token);
    }

    public function testAuthenticateTokenWrongUser()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        /** @var ApiKeyUserProvider|\PHPUnit_Framework_MockObject_MockObject $userProvider */
        $userProvider = $this->createMock(ApiKeyUserProvider::class);

        $authenticator = new ApiKeyAuthenticator();

        $token->expects($this->once())->method('getCredentials')->willReturn('key');

        $userProvider->expects($this->once())->method('getUsernameForApiKey')->with('key')->willReturn(null);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $authenticator->authenticateToken($token, $userProvider, 'api');
    }


    public function testAuthenticateToken()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);
        /** @var ApiKeyUserProvider|\PHPUnit_Framework_MockObject_MockObject $userProvider */
        $userProvider = $this->createMock(ApiKeyUserProvider::class);
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);

        $authenticator = new ApiKeyAuthenticator();

        $token->expects($this->once())->method('getCredentials')->willReturn('key');

        $userProvider->expects($this->once())->method('getUsernameForApiKey')->with('key')->willReturn('username');

        $userProvider->expects($this->once())->method('loadUserByUsername')->with('username')->willReturn($user);

        $user->expects($this->once())->method('getRoles')->willReturn([]);

        $authenticate = $authenticator->authenticateToken($token, $userProvider, 'api');

        $this->assertInstanceOf(PreAuthenticatedToken::class, $authenticate);
    }

    public function testSupportsToken()
    {
        /** @var PreAuthenticatedToken|\PHPUnit_Framework_MockObject_MockObject $preAuthenticatedToken */
        $preAuthenticatedToken = $this->createMock(PreAuthenticatedToken::class);

        $authenticator = new ApiKeyAuthenticator();

        $preAuthenticatedToken->expects($this->once())->method('getProviderKey')->willReturn('api');

        $currentToken = $authenticator->supportsToken($preAuthenticatedToken, 'api');
        $this->assertTrue($currentToken);
    }

    public function testCreateTokenFromQuery()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var HeaderBag|\PHPUnit_Framework_MockObject_MockObject $headerBug */
        $headerBug = $this->createMock(HeaderBag::class);
        /** @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject $parameterBag */
        $parameterBag = $this->createMock(ParameterBag::class);

        $request->headers = $headerBug;
        $request->query = $parameterBag;

        $authenticator = new ApiKeyAuthenticator();

        $headerBug->expects($this->once())->method('get')->willReturn(null);
        $parameterBag->expects($this->once())->method('get')->willReturn('token');

        $token = $authenticator->createToken($request, 'api');

        $this->assertInstanceOf(PreAuthenticatedToken::class, $token);
    }

    public function testCreateTokenFromHeaders()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var HeaderBag|\PHPUnit_Framework_MockObject_MockObject $headerBug */
        $headerBug = $this->createMock(HeaderBag::class);
        /** @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject $parameterBag */
        $parameterBag = $this->createMock(ParameterBag::class);

        $request->headers = $headerBug;
        $request->query = $parameterBag;

        $authenticator = new ApiKeyAuthenticator();

        $headerBug->expects($this->once())->method('get')->willReturn('token');

        $token = $authenticator->createToken($request, 'api');

        $this->assertInstanceOf(PreAuthenticatedToken::class, $token);
    }

    public function testNotCreateToken()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var HeaderBag|\PHPUnit_Framework_MockObject_MockObject $headerBug */
        $headerBug = $this->createMock(HeaderBag::class);
        /** @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject $parameterBag */
        $parameterBag = $this->createMock(ParameterBag::class);

        $request->headers = $headerBug;
        $request->query = $parameterBag;

        $authenticator = new ApiKeyAuthenticator();

        $headerBug->expects($this->once())->method('get')->willReturn(null);
        $parameterBag->expects($this->once())->method('get')->willReturn(null);

        $token = $authenticator->createToken($request, 'api');

        $this->assertNull($token);
    }


    public function testOnAuthenticationFailure()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var AuthenticationException|\PHPUnit_Framework_MockObject_MockObject $exception */
        $exception = $this->createMock(AuthenticationException::class);
        $authenticator = new ApiKeyAuthenticator();

        $exception->expects($this->once())->method('getMessageKey');
        $exception->expects($this->once())->method('getMessageData');

        $response = $authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
    }
}
