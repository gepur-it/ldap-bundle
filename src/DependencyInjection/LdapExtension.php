<?php

namespace GepurIt\LdapBundle\DependencyInjection;

use GepurIt\LdapBundle\Ldap\LdapConnection;
use GepurIt\LdapBundle\Security\LdapUserProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;

/**
 * Class LdapExtension
 * @package LdapBundle\DependencyInjection
 * @codeCoverageIgnore
 */
class LdapExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $this->initComponent($container, $config);
        $this->initLdapConnection($container, $config);
        $this->initLdapUserProvider($container, $config);
    }

    private function initComponent(ContainerBuilder $container, array $config)
    {
        $adapter = new Definition();
        $adapter->setClass(Adapter::class);
        $adapterConfig = [
            'host' => $config['ldap_host'],
            'port' => $config['ldap_port'],
            'encryption' => $config['ldap_encryption'],
            'options' => [
                'protocol_version' => 3,
                'referrals' => false
            ]

        ];
        $adapter->setArgument('$config', $adapterConfig);
        $container->setDefinition(Adapter::class, $adapter);

        $ldapDefinition = new Definition();
        $ldapDefinition->setClass(Ldap::class);
        $container->setDefinition(Ldap::class, $ldapDefinition);
        $container->setAlias(LdapInterface::class, Ldap::class);
    }

    private function initLdapConnection(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition(LdapConnection::class);
        $ldapDefinition = $container->getDefinition(Ldap::class);
        $definition->setArgument('$ldap', $ldapDefinition);
        $definition->setArgument('$baseDn', $config['ldap_dn']);
        $definition->setArgument('$searchQuery', $config['ldap_groups_search_query']);
        $definition->setArgument('$searchUser', $config['ldap_search_dn']);
        $definition->setArgument('$password', $config['ldap_search_password']);
    }

    private function initLdapUserProvider(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition(LdapUserProvider::class);
        $definition->setArgument('$groupName', $config['ldap_base_group_name']);
    }
}
