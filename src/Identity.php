<?php

namespace MakinaCorpus\ACL;

final class Identity
{
    /**
     * This is terribly ugly, but it will compute a unique identifier for an
     * object in a very terrible fashion, good enough for us anyway
     *
     * @param mixed $object
     *
     * @return string
     */
    static public function computeUniqueIdentifier($object)
    {
        $id = null;

        if (!is_object($object)) {
            if (is_scalar($object)) {
                return (string)$object;
            } else {
                return sha1(serialize($object));
            }
        }

        if (property_exists($object, 'id')) {
            $id = $object->id;
        }
        if (!$id && method_exists($object, 'getId')) {
            $id = $object->getId();
        }
        if (!$id && method_exists($object, 'id')) {
            $id = $object->id();
        }

        if ($id) {
            return get_class($object) . '#' . $id;
        }

        return spl_object_hash($object);
    }
}