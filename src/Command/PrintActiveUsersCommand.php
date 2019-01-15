<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 24.09.18
 * Time: 16:24
 */

namespace GepurIt\LdapBundle\Command;

use GepurIt\LdapBundle\Security\LdapUserProvider;
use GepurIt\User\Security\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PrintActiveUsersCommand
 * @package LdapBundle\Command
 * @codeCoverageIgnore
 */
class PrintActiveUsersCommand extends Command
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /**
     * @var LdapUserProvider
     */
    private $ldapUserProvider;

    public function __construct(LdapUserProvider $ldapUserProvider)
    {
        $this->ldapUserProvider = $ldapUserProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ldap:active:print')
            ->setDescription('Print active ldap users')
            ->setHelp('Example usage: bin/console ldap:active:print');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->input  = $input;
        $this->output = $output;

        $table = new Table($output);
        $table
            ->setHeaders(['sid', 'name', 'login'])
            ->setRows(
                array_map(
                    function ($m) {
                        /** @var User $m */
                        return [
                            'name'  => $m->getName(),
                            'login' => $m->getLogin(),
                            'sid'   => $m->getLdapSid(),
                        ];
                    },
                    $this->ldapUserProvider->getActiveUsers()
                )
            );

        $table->render();
    }
}
