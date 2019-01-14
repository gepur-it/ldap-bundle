<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 14.12.17
 * Time: 13:44
 */
namespace LdapBundle\TestsFunctional;

use GepurIt\LdapBundle\DataFixtures\LdapFixtures;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Entity\LdapRole;
use PHPUnit\Framework\TestCase;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
/**
 * Class LdapResourceRepositoryTest
 * @package LdapBundle\TestsFunctional
 */
class LdapResourceRepositoryTest extends TestCase
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

    public function testFindAllFull()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resources = $repository->findAllFull();

        $this->assertEquals(7, count($resources));

        $resource = array_shift($resources);

        $this->assertInstanceOf(LdapResource::class, $resource);
    }

    public function testNotFindAllFull()
    {

        $this->purgeFixtures();

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resources = $repository->findAllFull();

        $this->assertEquals(0, count($resources));
    }

    public function testFindOneFullByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->findOneFullByResource('Почта клиентов');

        $this->assertObjectHasAttribute('roleAccesses', $resource);

        foreach ($resource->getRoleAccesses() as $roleAccess) {
            $this->assertInstanceOf(LdapRole::class, $roleAccess->getRole());
        }

        $this->assertInstanceOf(LdapResource::class, $resource);
    }

    public function testNotFindOneFullByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->findOneFullByResource('wrong_resource');

        $this->assertNull($resource);
    }

    public function testFindOneByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->findOneByResource('Почта клиентов');

        $this->assertInstanceOf(LdapResource::class, $resource);
    }

    public function testNotFindOneByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->findOneByResource('wrong_resource');

        $this->assertNull($resource);
    }

    public function testExistsByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->existsByResource('Почта клиентов');

        $this->assertTrue($resource);
    }

    public function testNotExistsByResource()
    {
        $fixtures = [
            LdapFixtures::class
        ];

        $this->loadFixtures($fixtures);

        $entityManager = $this->kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $repository = $entityManager->getRepository(LdapResource::class);

        $resource = $repository->existsByResource('wrong_resource');

        $this->assertFalse($resource);
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
