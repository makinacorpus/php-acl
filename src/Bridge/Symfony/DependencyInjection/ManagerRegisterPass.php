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
        if (!$container->hasDefinition('acl.manager') && !$container->hasAlias('acl.manager')) {
            return;
        }

        $entryStores        = $this->collectTaggedServices($container, 'acl.entry_store', EntryStoreInterface::class);
        $resourceCollectors = $this->collectTaggedServices($container, 'acl.entry_collector', EntryCollectorInterface::class);
        $profileCollectors  = $this->collectTaggedServices($container, 'acl.profile_collector', ProfileCollectorInterface::class);
        $resourceConverters = $this->collectTaggedServices($container, 'acl.resource_converter', ResourceConverterInterface::class);

        $definition = $container->getDefinition('acl.manager');
        $definition->setArguments([
            $this->mapIdToReference($entryStores),
            $this->mapIdToReference($resourceCollectors),
            $this->mapIdToReference($profileCollectors),
            $this->mapIdToReference($resourceConverters),
            new Reference('acl.permission_map')
        ]);
    }
}
