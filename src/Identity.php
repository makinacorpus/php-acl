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
     * @return null|string
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

        // @todo we have a problem there, spl_object_hash() cannot be used
        //   for proof we already had collisions within unit tests
        return null;
    }

    /**
     * Get string representation of the given item
     */
    static public function getStringRepresentation(string $type, string $id) : string
    {
        return $type . '#' . $id;
    }

    /**
     * From a string reprensentation, give type and identifier
     */
    static public function fromString(string $string) : array
    {
        // We do not check with false because 0 would mean the delimiter
        // is the first char, and this would mean there is no type
        if (!strpos($string, '#')) {
            throw new \InvalidArgumentException("%s: is not a (type, id) representation");
        }

        return explode('#', $string, 2);
    }
}
