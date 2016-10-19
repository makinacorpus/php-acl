<?php

namespace MakinaCorpus\ACL\Impl\Symfony\DependencyInjection;

use MakinaCorpus\ACL\Collector\EntryCollectorInterface;
use MakinaCorpus\ACL\Store\EntryStoreInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Registers all dynamic ACL voter dependencies, unregister it if there's not.
 */
class DynamicACLRegisterPass implements CompilerPassInterface
{
    use RegisterPassTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // If there's no ACL manager, just drop everything
        if (!$container->hasDefinition('acl.manager') && !$container->hasAlias('acl.manager')) {
            $this->unregister($container, 'acl.voter.dynamic');
            return;
        }

        $collectors = $this->collectTaggedServices($container, 'acl.entry_collector', EntryCollectorInterface::class);
        if (empty($collectors)) {
            $this->unregister($container, 'acl.voter.dynamic');
            return;
        }

        $stores = $this->collectTaggedServices($container, 'acl.entry_store', EntryStoreInterface::class);

        $definition = $container->getDefinition('acl.voter.dynamic');
        $definition->setArguments([
            $this->mapIdToReference($stores),
            $this->mapIdToReference($collectors),
        ]);
    }
}
