<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 14.12.17
 * Time: 13:58
 */

namespace LdapBundle\TestsFunctional;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use GepurIt\LdapBundle\DataFixtures\LdapFixtures;
use GepurIt\LdapBundle\Entity\LdapRole;
use PHPUnit\Framework\TestCase;

/**
 * Class LdapRoleRepositoryTest
 * @package LdapBundle\TestsFunctional
 */
class LdapRoleRepositoryTest extends TestCase
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

    public function testFindOneByRole()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRole::class);

        $role = $repository->findOneByRole('Разработчики');

        $this->assertInstanceOf(LdapRole::class, $role);
    }

    public function testNotFindOneByRole()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRole::class);

        $role = $repository->findOneByRole('wrong_role');

        $this->assertNull($role);
    }

    public function testExistsByRole()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRole::class);

        $role = $repository->existsByRole('Разработчики');

        $this->assertTrue($role);
    }

    public function testNotExistsByRole()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapRole::class);

        $role = $repository->existsByRole('wrong_role');

        $this->assertFalse($role);
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
