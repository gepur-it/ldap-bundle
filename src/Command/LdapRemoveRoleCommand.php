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
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class LdapRemoveRoleCommand
 * @package GepurIt\LdapBundle\Command
 */
class LdapRemoveRoleCommand extends Command
{
    private LdapGroupsProvider $ldapGroupsProvider;
    private EntityManagerInterface $entityManager;

    /**
     * LdapRemoveRoleCommand constructor.
     *
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
            ->setName('ldap:role:remove')
            ->setDescription('Remove existing role from the ldap_role table.')
            ->addArgument('role_name', InputArgument::REQUIRED, 'Existing role name.');
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
        /** @var LdapRoleRepository $ldapRoleRepository */
        $ldapRoleRepository = $this->entityManager->getRepository(LdapRole::class);
        if (!$ldapRoleRepository->existsByRole($roleName)) {
            $output->writeln(sprintf('<info>Group with name %s not exist.</info>', $roleName));

            return 0;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>Are you sure? [y|n]:</question>', false);

        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        $this->ldapGroupsProvider->forgetGroup($roleName);
        $output->writeln('<fg=green>Ldap role removed!</>');

        return 0;
    }
}
