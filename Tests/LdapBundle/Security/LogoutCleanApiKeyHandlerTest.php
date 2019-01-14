<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 11:21
 */

namespace GepurIt\LdapBundle\Tests\Security;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use GepurIt\LdapBundle\Document\UserApiKey;
use GepurIt\LdapBundle\Security\LogoutCleanApiKeyHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class LogoutCleanApiKeyHandlerTest
 * @package LdapBundle\Security
 */
class LogoutCleanApiKeyHandlerTest extends TestCase
{
    public function testLogout()
    {
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        /** @var Response|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->createMock(Response::class);

        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);

        /** @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject $documentManager */
        $documentManager = $this->createMock(DocumentManager::class);

        /** @var Builder|\PHPUnit_Framework_MockObject_MockObject $queryBuilder */
        $queryBuilder = $this->createMock(Builder::class);

        /** @var Query|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->createMock(Query::class);

        $handler = new LogoutCleanApiKeyHandler($documentManager);

        $token->expects($this->once())
            ->method('getUsername')->willReturn('username');

        $documentManager->expects($this->once())
            ->method('createQueryBuilder')
            ->with(UserApiKey::class)
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('remove')
            ->willReturn($queryBuilder);

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

        $handler->logout($request, $response, $token);
    }
}
