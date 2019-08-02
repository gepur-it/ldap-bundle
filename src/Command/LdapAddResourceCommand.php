<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 13.11.17
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Entity\LdapResource;
use GepurIt\LdapBundle\Repository\LdapResourceRepository;
use GepurIt\LdapBundle\Security\LdapResourcesProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LdapAddResourceCommand
 * @package LdapBundle\Command
 */
class LdapAddResourceCommand extends Command
{
    /** @var LdapResourcesProvider */
    private $resourceProvider;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /**
     * LdapAddResourceCommand constructor.
     *
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
            ->setName('ldap:resource:add')
            ->setDescription('Add new resource to the ldap_resource table if not exists.')
            ->addArgument('resource_name', InputArgument::REQUIRED, 'New resource name.');
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
        /** @var LdapResourceRepository $resourceRepository */
        $resourceRepository = $this->entityManager->getRepository(LdapResource::class);
        if ($resourceRepository->existsByResource($resourceName)) {
            $output->writeln(sprintf('<info>Resource %s already exist.</info>', $resourceName));

            return;
        }

        $this->resourceProvider->createResource($resourceName);
        $output->writeln('<fg=green>Ldap resource created!</>');
    }
}
