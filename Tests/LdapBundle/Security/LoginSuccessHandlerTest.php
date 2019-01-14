<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 30.10.17
 */

namespace GepurIt\LdapBundle\TestsBundle\Tests\Security;

use GepurIt\ActionLoggerBundle\Logger\ActionLoggerInterface;
use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use GepurIt\LdapBundle\Security\LoginSuccessHandler;
use GepurIt\User\Security\User;
use GepurIt\LdapBundle\Document\UserApiKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class LoginSuccessHandlerTest
 * @package AppBundle\Tests\Security
 */
class LoginSuccessHandlerTest extends TestCase
{
    /**
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testOnAuthenticationSuccessJson()
    {
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var \GepurIt\User\Security\User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        /** @var Builder|\PHPUnit_Framework_MockObject_MockObject $queryBuilder */
        $queryBuilder = $this->createMock(Builder::class);
        /** @var Query|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->createMock(Query::class);

        /** @var UserApiKey|\PHPUnit_Framework_MockObject_MockObject $userApiKey */
        $userApiKey = $this->createMock(UserApiKey::class);

        $logger = $this->getLoggerMock();
        $httpUtils = $this->getHttpUtilsMock();
        $options = [
            'always_use_default_target_path' => true,
            'default_target_path'            => 'default_target_path',
        ];

        $request = $this->getRequestMock();
        $token = $this->getTokenInterfaceMock();

        $request->expects($this->once())->method('getRequestFormat')->willReturn('json');

        $token->expects($this->once())->method('getUser')->willReturn($user);


        $documentManager->expects($this->once())
            ->method('createQueryBuilder')
            ->with(UserApiKey::class)
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('remove')
            ->willReturn($queryBuilder);

        $user->expects($this->exactly(2))->method('getUsername')->willReturn('username');

        $queryBuilder->expects($this->once())
            ->method('field')
            ->with('username')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('equals')
            ->with('username')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);
        $query->expects($this->once())->method('execute');

        $userApiKey->expects($this->any())->method('setUsername')->with('username');
        $userApiKey->expects($this->any())->method('setApiKey')->with('key');
        $userApiKey->expects($this->any())->method('setUserId')->with('id');
        $userApiKey->expects($this->any())->method('setObjectGUID')->with('objectGUID');

        $documentManager->expects($this->once())->method('persist');
        $documentManager->expects($this->once())->method('flush');

        $handler = new LoginSuccessHandler(
            $logger,
            $httpUtils,
            $documentManager,
            $options
        );

        $response = $handler->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function testOnAuthenticationSuccess()
    {
        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */

        $logger = $this->getLoggerMock();
        $httpUtils = $this->getHttpUtilsMock();
        $options = [
            'always_use_default_target_path' => true,
            'default_target_path'            => 'default_target_path',
        ];

        $request = $this->getRequestMock();
        $token = $this->getTokenInterfaceMock();

        $request->expects($this->once())->method('getRequestFormat')->willReturn('html');

        $logger->expects($this->once())
            ->method('log')
            ->with('login', 'User Login');

        $httpUtils->expects($this->once())
            ->method('createRedirectResponse')
            ->with($request, 'default_target_path');

        $handler = new LoginSuccessHandler(
            $logger,
            $httpUtils,
            $documentManager,
            $options
        );

        $handler->onAuthenticationSuccess($request, $token);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionLoggerInterface
     */
    public function getLoggerMock()
    {
        $mock = $this->getMockBuilder(ActionLoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpUtils
     */
    private function getHttpUtilsMock()
    {
        $mock = $this->getMockBuilder(HttpUtils::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'createRedirectResponse',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    private function getTokenInterfaceMock()
    {
        $mock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    '__toString',
                    'getRoles',
                    'getCredentials',
                    'getUser',
                    'setUser',
                    'getUsername',
                    'isAuthenticated',
                    'setAuthenticated',
                    'eraseCredentials',
                    'getAttributes',
                    'setAttributes',
                    'hasAttribute',
                    'getAttribute',
                    'setAttribute',
                    'serialize',
                    'unserialize',
                ]
            )
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function getRequestMock()
    {
        $mock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        return $mock;
    }
}

