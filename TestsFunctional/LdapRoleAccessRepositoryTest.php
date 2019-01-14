<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 14.12.17
 * Time: 13:45
 */

namespace LdapBundle\TestsFunctional;

use GepurIt\LdapBundle\DataFixtures\LdapFixtures;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;
use PHPUnit\Framework\TestCase;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class LdapRoleAccessRepositoryTest
 * @package LdapBundle\TestsFunctional
 */
class LdapRoleAccessRepositoryTest extends TestCase
{
    /**
     * @var \AppKernel
     */
    public $kernel;

    /**
     * @var SymfonyFixturesLoader
     */
    public $fixturesLoader;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->kernel = new \AppKernel('test', false);
        $this->kernel->boot();

        $serviceContainer = $this->kernel->getContainer()
            ->get('service_container');

        $this->fixturesLoader = new SymfonyFixturesLoader($serviceContainer);
    }


    public function testFindByToken()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRoleAccess::class);
        $token = new UsernamePasswordToken('test', null, 'default', ['Разработчики']);

        $accesses = $repository->findByToken($token);

        $this->assertEquals(1, count($accesses));

        $access = array_shift($accesses);

        $this->assertInstanceOf(LdapRoleAccess::class, $access);
    }


    public function testNotFindByToken()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRoleAccess::class);
        $token = new UsernamePasswordToken('test', null, 'default', ['wrong_role']);

        $accesses = $repository->findByToken($token);

        $this->assertEquals(0, count($accesses));
    }

    /**
     * @param array $fixtures
     * @return void
     */
    public function loadFixtures(array $fixtures)
    {
        foreach ($fixtures as $fixture) {
            if (class_exists($fixture)) {
                $this->fixturesLoader->addFixture(new $fixture);
            }
        }

        $fixtures = $this->fixturesLoader->getFixtures();

        if (!$fixtures) {
            throw new \InvalidArgumentException(
                'Could not find any fixture services to load.'
            );
        }

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($fixtures);
    }

    /**
     * Purge fixtures
     */
    public function purgeFixtures()
    {
        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);

        $purger->purge();
    }
}
