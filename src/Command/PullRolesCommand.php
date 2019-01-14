<?php

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PullRolesCommand
 * @package LdapBundle\Command
 */
class PullRolesCommand extends Command
{
    /**
     * @var LdapGroupsProvider
     */
    private $ldapGroupsProvider;

    /** @var  EntityManagerInterface */
    private $entityManager;

    /**
     * PullRolesCommand constructor.
     *
     * @param LdapGroupsProvider     $ldapGroupsProvider
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LdapGroupsProvider $ldapGroupsProvider, EntityManagerInterface $entityManager)
    {
        $this->ldapGroupsProvider = $ldapGroupsProvider;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('ldap:pull-roles')
            ->setDescription('Pulls roles from ldap server')
            ->setHelp('Sync roles from ldap server and create default access rules');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupNames = $this->ldapGroupsProvider->loadRemoteGroups();
        /** @var LdapRoleRepository $repository */
        $repository = $this->entityManager->getRepository(LdapRole::class);
        foreach ($groupNames as $groupName) {
            /** @var LdapRole $ldapRole */
            if ($repository->existsByRole($groupName)) {
                continue;
            }
            $ldapRole = new LdapRole($groupName);
            $this->entityManager->persist($ldapRole);
            $this->entityManager->flush($ldapRole);
        }
    }
}
