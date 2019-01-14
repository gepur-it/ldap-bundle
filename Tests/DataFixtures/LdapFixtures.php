<?php
/**
 * Created by PhpStorm.
 * User: pavlov
 * Date: 14.12.17
 * Time: 13:29
 */
namespace GepurIt\LdapBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Entity\LdapRoleAccess;

/**
 * Class LdapFixtures
 * @package LdapBundle\DataFixtures
 * @codeCoverageIgnore
 */
class LdapFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $role_fixtures = $this->getRoleFixturesData();

        foreach ($role_fixtures as $fixture) {
            $role = new LdapRole($fixture);

            $manager->persist($role);
            $manager->flush();
        }

        $resource_fixtures = $this->getResourceFixturesData();

        foreach ($resource_fixtures as $fixture) {
            $resource = new LdapResource();
            $resource->setResource($fixture);
            $manager->persist($resource);
            $manager->flush();
        }

        $role = $manager->getRepository(LdapRole::class)->findOneByRole('Разработчики');
        $resource = $manager->getRepository(LdapResource::class)->findOneByResource('Почта клиентов');

        $access = new LdapRoleAccess($role, $resource);
        $access->setPermissionMask(1);
        $manager->persist($access);
        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getResourceFixturesData()
    {
        return [
            'Быстрые ответы',
            'Отчёты',
            'Отчёты почты',
            'Почта клиентов',
            'Права пользователей',
            'Прикрепления',
            'Спам'
        ];
    }

    /**
     * @return array
     */
    protected function getRoleFixturesData()
    {
        return [
            'Администратор пользователей',
            'Корректировка Товаров',
            'Разработчики'
        ];
    }
}
