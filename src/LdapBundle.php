<?php

namespace GepurIt\LdapBundle;

use GepurIt\LdapBundle\DependencyInjection\Compiler\EntryPointCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LdapBundle
 * @package LdapBundle
 */
class LdapBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EntryPointCompilerPass());
    }
}
