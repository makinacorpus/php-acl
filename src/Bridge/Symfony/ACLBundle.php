<?php

namespace MakinaCorpus\ACL\Bridge\Symfony;

use MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection\AuthorizationAwareRegisterPass;
use MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection\ManagerAwareRegisterPass;
use MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection\ManagerRegisterPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ACLBundle extends Bundle
{
   /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('core.yml');

        // Attempt Drupal detection and automatically register services along.
        if (interface_exists('\\Drupal\\Core\\DependencyInjection\\ServiceProviderInterface')) {
            $loader->load('drupal.yml');
        }

        $container->addCompilerPass(new ManagerRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new ManagerAwareRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new AuthorizationAwareRegisterPass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
