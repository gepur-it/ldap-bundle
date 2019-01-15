<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 27.11.17
 */

namespace GepurIt\LdapBundle\DependencyInjection\Compiler;

use GepurIt\LdapBundle\Security\GepurLdapEntryPoint;
use GepurIt\LdapBundle\Security\GepurLdapFormListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EntryPointCompilerPass
 * @package LdapBundle\DependencyInjection\Compiler
 * @codeCoverageIgnore
 */
class EntryPointCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('security.authentication.form_entry_point');
        $definition->setClass(GepurLdapEntryPoint::class);
        $definition = $container->getDefinition('security.authentication.listener.form');
        $definition->setClass(GepurLdapFormListener::class);
    }
}

