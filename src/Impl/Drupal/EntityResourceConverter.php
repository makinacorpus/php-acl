<?php

namespace MakinaCorpus\ACL\Impl\Drupal;

use Drupal\Core\Entity\EntityInterface;

use MakinaCorpus\ACL\Converter\ResourceConverterInterface;
use MakinaCorpus\ACL\Resource;

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
    }
}