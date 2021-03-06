<?php

namespace MakinaCorpus\ACL\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\ACL\Bridge\Symfony\AuthorizationAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * For all objects that use the ManagerAwareTrait extend their definition to
 * inject the manager from
 *
 * @codeCoverageIgnore
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
                    $definition->addMethodCall('setAuthorizationChecker', [new Reference($this->authorizationCheckerId)]);
                }
            } catch (\ReflectionException $e) {
                // Class does not seem to exists, do nothing.
            }
        }
    }
}
