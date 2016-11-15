<?php

namespace MakinaCorpus\ACL\Impl\Symfony;

use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * From a Drupal user, give a 'user' typed profile to consume
 */
class UserGrantProfileCollector implements ProfileCollectorInterface
{
    public function collectProfiles(ProfileSetBuilder $builder)
    {
        $object = $builder->getObject();

        if ($object instanceof TokenInterface) {
            $object = $object->getUser();
        }

        if ($object instanceof UserInterface) {
            if (method_exists($object, 'getId')) {
                $builder->add('user', $object->getId());
            } else {
                $builder->add('user', $object->getUsername());
            }
        }
    }
}
