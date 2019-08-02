<?php
/**
 * Created by PhpStorm.
 * User: mari
 * Date: 20.12.17
 * Time: 11:57
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use GepurIt\LdapBundle\Contracts\ErpUserProviderInterface;
use GepurIt\User\Entity\UserProfile;
use GepurIt\User\Repository\UserProfileRepository;
use GepurIt\User\Security\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FillNameInUserProfileCommand
 * @package LdapBundle\Command
 */
class FillNameInUserProfileCommand extends Command
{
    /** @var ErpUserProviderInterface */
    private $ldapUserProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * FillNameInUserProfileCommand constructor.
     * @param ErpUserProviderInterface $ldapUserProvider
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ErpUserProviderInterface $ldapUserProvider, EntityManagerInterface $entityManager)
    {
        $this->ldapUserProvider = $ldapUserProvider;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ldap:manager-name:fill')
            ->setDescription('Fill manager_name in table user_profile if this field is empty.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var UserProfileRepository $profileRepository */
        $profileRepository = $this->entityManager->getRepository(UserProfile::class);
        /** @var UserProfile[] $userProfiles */
        $userProfiles = $profileRepository->findBy(['managerName' => '']);
        if (empty($userProfiles)) {
            $output->writeln(sprintf('<info>No profiles with empty names.</info>'));

            return;
        }

        foreach ($userProfiles as $userProfile) {
            $managerId = $userProfile->getManagerId();
            /** @var User $user */
            $user = $this->ldapUserProvider->loadUserBySid($managerId);
            $userProfile->setManagerName($user->getName());
            $this->entityManager->persist($userProfile);
        }
        $this->entityManager->flush();
        $output->writeln('<fg=green>Manager_names was renewed in table user_profile!</>');
    }
}
