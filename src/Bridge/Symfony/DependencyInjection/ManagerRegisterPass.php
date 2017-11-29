<?php

namespace MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Store\EntryStoreInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers all dynamic ACL voter dependencies, unregister it if there's not.
 *
 * @codeCoverageIgnore
 */
class ManagerRegisterPass implements CompilerPassInterface
{
    use RegisterPassTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // If there's no ACL manager, just drop everything
        if (!$container->hasDefinition('php_acl.manager') && !$container->hasAlias('php_acl.manager')) {
            return;
        }

        $entryStores        = $this->collectTaggedServices($container, 'php_acl.entry_store', EntryStoreInterface::class);
        $resourceCollectors = $this->collectTaggedServices($container, 'php_acl.entry_collector', EntryCollectorInterface::class);
        $profileCollectors  = $this->collectTaggedServices($container, 'php_acl.profile_collector', ProfileCollectorInterface::class);
        $resourceConverters = $this->collectTaggedServices($container, 'php_acl.resource_converter', ResourceConverterInterface::class);

        $definition = $container->getDefinition('php_acl.manager');
        $definition->setArguments([
            $this->mapIdToReference($entryStores),
            $this->mapIdToReference($resourceCollectors),
            $this->mapIdToReference($profileCollectors),
            $this->mapIdToReference($resourceConverters),
            new Reference('php_acl.permission_map')
        ]);
    }
}
