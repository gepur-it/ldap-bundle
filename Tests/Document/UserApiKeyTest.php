<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 15.01.18
 * Time: 10:30
 */
namespace GepurIt\LdapBundle\Tests\Document;

use GepurIt\LdapBundle\Document\UserApiKey;
use PHPUnit\Framework\TestCase;

/**
 * Class UserApiKeyTest
 * @package LdapBundle\Document
 */
class UserApiKeyTest extends TestCase
{
    /**
     * @param $actual
     * @param $expectation
     * @dataProvider dataProvider
     */
    public function testGetterAndSetter($actual, $expectation)
    {
        $entity = new UserApiKey();
        $entity->setApiKey($actual['key']);
        $this->assertEquals($expectation['key'], $entity->getApiKey());
        $entity->setUsername($actual['username']);
        $this->assertEquals($expectation['username'], $entity->getUsername());
        $entity->setUserId($actual['userId']);
        $this->assertEquals($expectation['userId'], $entity->getUserId());
        $entity->setLastActivity($actual['lastActivity']);
        $this->assertEquals($expectation['lastActivity'], $entity->getLastActivity());

        $entity->updateLastActivity();
        $this->assertInstanceOf(\DateTime::class, $entity->getLastActivity());
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'correct' => [
                [
                    'key' => 'id',
                    'username' => 'name',
                    'userId' => 'user_id_2',
                    'lastActivity' => new \DateTime('2018-01-01 00:00:00')
                ],
                [
                    'key' => 'id',
                    'username' => 'name',
                    'userId' => 'user_id_2',
                    'lastActivity' => new \DateTime('2018-01-01 00:00:00')
                ]
            ]
        ];
    }
}
