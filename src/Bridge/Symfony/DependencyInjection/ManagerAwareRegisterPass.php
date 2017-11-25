<?php

namespace MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\ACL\ManagerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * For all objects that use the ManagerAwareTrait extend their definition to
 * inject the manager from
 */
class ManagerAwareRegisterPass implements CompilerPassInterface
{
    private $aclManagerId = 'acl.manager';

    public function __construct($aclManagerId = 'acl.manager')
    {
        $this->aclManagerId = $aclManagerId;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasAlias($this->aclManagerId) && !$container->has($this->aclManagerId)) {
            return;
        }

        foreach ($container->getDefinitions() as $definition) {
            try {
                if (is_subclass_of($definition->getClass(), ManagerAwareInterface::class)) {
                    $definition->addMethodCall('setACLManager', [new Reference($this->aclManagerId)]);
                }
            } catch (\ReflectionException $e) {
                // Class does not seem to exists, do nothing.
            }
        }
    }
}
