<?php

namespace GepurIt\LdapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package LdapBundle\DependencyInjection
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ldap');

        $rootNode
            ->children()
                ->scalarNode('ldap_dn')
                    ->cannotBeEmpty()
                    ->defaultValue('dc=example,dc=com')
                ->end()
                ->scalarNode('ldap_host')
                    ->cannotBeEmpty()
                    ->defaultValue('127.0.0.1')
                ->end()
                ->scalarNode('ldap_port')
                    ->defaultNull()
                ->end()
                ->scalarNode('ldap_encryption')
                    ->cannotBeEmpty()
                    ->defaultValue('tls')
                ->end()
                ->scalarNode('ldap_search_dn')
                    ->cannotBeEmpty()
                    ->defaultValue('cn=read-only-admin,dc=example,dc=com')
                ->end()
                ->scalarNode('ldap_search_password')
                    ->cannotBeEmpty()
                    ->defaultValue('password')
                ->end()
                ->scalarNode('ldap_groups_search_query')
                    ->cannotBeEmpty()
                    ->defaultValue('OU=???????,dc=GEPUR,dc=AD')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
