<?php
/**
 * Gepur ERP.
 * Author: Andrii Yakovlev <yawa20@gmail.com>
 * Date: 02.08.19
 */
declare(strict_types=1);

namespace GepurIt\LdapBundle\DependencyInjection;

use GepurIt\LdapBundle\Ldap\LdapConnection;
use GepurIt\LdapBundle\Ldap\UserProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->initLdapConnection($container, $config);
        $this->initLdapUserProvider($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function initLdapConnection(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition(LdapConnection::class);
        $definition->setArgument('$baseDn', $config['ldap_dn']);
        $definition->setArgument('$searchQuery', $config['ldap_groups_search_query']);
        $definition->setArgument('$searchUser', $config['ldap_search_dn']);
        $definition->setArgument('$password', $config['ldap_search_password']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function initLdapUserProvider(ContainerBuilder $container, array $config)
    {
        $definition = $container->findDefinition(UserProvider::class);
        $definition->setArgument('$groupName', $config['ldap_base_group_name']);
    }
}
