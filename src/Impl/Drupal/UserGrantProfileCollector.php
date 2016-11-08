<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use Drupal\Core\Session\AccountInterface;

use MakinaCorpus\ACL\Collector\ProfileCollectorInterface;
use MakinaCorpus\ACL\Collector\ProfileSetBuilder;

/**
 * From a Drupal user, give a 'user' typed profile to consume
 */
class UserGrantProfileCollector implements ProfileCollectorInterface
{
    public function collectProfiles(ProfileSetBuilder $builder)
    {
        $object = $builder->getObject();

        // Allow integer values to be used as a user account in Drupal context
        if (is_int($object) && 0 <= $object) {
            $builder->add('user', (int)$object);
            return;
        }

        // Easy one, Entity API usage
        if ($object instanceof AccountInterface) {
            $builder->add('user', (int)$object->id());
            return;
        }

        // Else attempt to guess whether or not it's an account
        if (is_object($object) && !empty($object->uid)) {
            $builder->add('user', (int)$object->uid);
            return;
        }
    }
}
