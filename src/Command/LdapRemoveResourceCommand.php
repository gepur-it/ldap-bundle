<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Security\LdapResourcesProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class LdapRemoveResourceCommand
 * @package LdapBundle\Command
 */
class LdapRemoveResourceCommand extends Command
{
    /** @var LdapResourcesProvider $resourceProvider */
    private $resourceProvider;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /**
     * LdapRemoveResourceCommand constructor.
     * @param LdapResourcesProvider $resourceProvider
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LdapResourcesProvider $resourceProvider, EntityManagerInterface $entityManager)
    {
        $this->resourceProvider = $resourceProvider;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ldap:resource:remove')
            ->setDescription('Remove existing resource from the ldap_resource table.')
            ->addArgument('resource_name', InputArgument::REQUIRED, 'Existing resource name.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resourceName = $input->getArgument('resource_name');

        $resourceRepository = $this->entityManager->getRepository(LdapResource::class);
        if (!$resourceRepository->existsByResource($resourceName)) {
            $output->writeln(sprintf('<info>Resource with name %s not exist.</info>', $resourceName));

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>Are you sure? [y|n]:</question>', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $this->resourceProvider->removeResource($resourceName);
        $output->writeln('<fg=green>Ldap resource removed!</>');
    }
}
