<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapRole;
use GepurIt\LdapBundle\Repository\LdapRoleRepository;
use GepurIt\LdapBundle\Security\LdapGroupsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapAddRoleCommand
 * @package GepurIt\LdapBundle\Command
 */
class LdapAddRoleCommand extends Command
{
    /** @var LdapGroupsProvider */
    private LdapGroupsProvider $ldapGroupsProvider;

    /** @var EntityManagerInterface $entityManager */
    private EntityManagerInterface $entityManager;

    /**
     * LdapAddRoleCommand constructor.
     * @param LdapGroupsProvider $resourceProvider
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LdapGroupsProvider $resourceProvider, EntityManagerInterface $entityManager)
    {
        $this->ldapGroupsProvider = $resourceProvider;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ldap:role:add')
            ->setDescription('Add new role to the ldap_role table if not exists.')
            ->addArgument('role_name', InputArgument::REQUIRED, 'New role name.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roleName = $input->getArgument('role_name');

        /** @var LdapRoleRepository $ldapRepository */
        $ldapRepository = $this->entityManager->getRepository(LdapRole::class);
        if ($ldapRepository->existsByRole($roleName)) {
            $output->writeln(sprintf('<info>Role %s already exist.</info>', $roleName));

            return 0;
        }

        $this->ldapGroupsProvider->rememberGroup($roleName);
        $output->writeln('<fg=green>Ldap role created!</>');
        return 0;
    }
}
