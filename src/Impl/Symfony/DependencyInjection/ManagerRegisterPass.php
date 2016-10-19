<?php

namespace MakinaCorpus\ACL\Impl\Symfony\DependencyInjection;

use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Voter\VoterInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $voters             = $this->collectTaggedServices($container, 'acl.voter', VoterInterface::class);
        $profileCollectors  = $this->collectTaggedServices($container, 'acl.profile_collector', ProfileCollectorInterface::class);
        $resourceConverters = $this->collectTaggedServices($container, 'acl.resource_converter', ResourceConverterInterface::class);

        $definition = $container->getDefinition('acl.manager');
        $definition->setArguments([
            $this->mapIdToReference($voters),
            $this->mapIdToReference($profileCollectors),
            $this->mapIdToReference($resourceConverters),
        ]);
    }
}
