<?php

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LdapAddRoleCommand extends Command
{
    /** @var LdapGroupsProvider */
    private $ldapGroupsProvider;
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(LdapGroupsProvider $resourceProvider, EntityManagerInterface $entityManager)
    {
        $this->ldapGroupsProvider = $resourceProvider;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ldap:add-role')
            ->setDescription('Add new role to the ldap_role table if not exists.')
            ->addArgument('role_name', InputArgument::REQUIRED, 'New role name.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleName = $input->getArgument('role_name');

        /** @var LdapRoleRepository $ldapRepository */
        $ldapRepository = $this->entityManager->getRepository(LdapRole::class);
        if ($ldapRepository->existsByRole($roleName)) {
            $output->writeln(sprintf('<info>Role %s already exist.</info>', $roleName));

            return;
        }

        $this->ldapGroupsProvider->rememberGroup($roleName);
        $output->writeln('<fg=green>Ldap role created!</>');
    }
}
