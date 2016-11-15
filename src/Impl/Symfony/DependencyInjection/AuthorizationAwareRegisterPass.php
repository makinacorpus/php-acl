<?php

namespace MakinaCorpus\ACL\Impl\Symfony\DependencyInjection;

use MakinaCorpus\ACL\Impl\Symfony\AuthorizationAwareInterface;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * For all objects that use the ManagerAwareTrait extend their definition to
 * inject the manager from
 */
class AuthorizationAwareRegisterPass implements CompilerPassInterface
{
    private $authorizationCheckerId;

    public function __construct($authorizationCheckerId = 'security.authorization_checker')
    {
        $this->authorizationCheckerId = $authorizationCheckerId;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasAlias($this->authorizationCheckerId) && !$container->has($this->authorizationCheckerId)) {
            return;
        }

        foreach ($container->getDefinitions() as $definition) {
            try {
                if (is_subclass_of($definition->getClass(), AuthorizationAwareInterface::class)) {
                    $definition->addMethodCall('setAuthorizatonChecker', [new Reference($this->authorizationCheckerId)]);
                }
            } catch (\ReflectionException $e) {
                // Class does not seem to exists, do nothing.
            }
        }
    }
}
