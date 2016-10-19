<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use Drupal\Core\Entity\EntityInterface;

use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Resource;

/**
 * Converts many Drupal things into resource:
 *   - Drupal 8 style entities to ith 'entity_type' as type and
 *     'entity_id' as identifier
 *   - Drupal 7 nodes to with 'node' as type and 'nid' as identifier
 *   - Drupal 7 users to with 'user' as type and 'uid' as identifier
 */
class EntityResourceConverter implements ResourceConverterInterface
{
    public function convert ($object)
    {
        if (!is_object($object)) {
            return;
        }
        if ($object instanceof EntityInterface) {
            return new Resource($object->getEntityTypeId(), $object->id());
        }
        if (property_exists($object, 'nid') && property_exists($object, 'vid') && property_exists($object, 'type')) {
            return new Resource('node', $object->nid);
        }
        if (property_exists($object, 'uid') && property_exists($object, 'name') && property_exists($object, 'mail')) {
            return new Resource('user', $object->uid);
        }
    }
}