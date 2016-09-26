<?php

namespace MakinaCorpus\ACL;

/**
 * Resource and profile types have this in common.
 *
 * Implementation is immutable by default.
 */
trait IdentityTrait
{
    private $type;
    private $id;

    /**
     * Default constructor
     *
     * @param string $type
     * @param int|string $id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * Get object type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get object identifier
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Is this object the same
     *
     * @param mixed $object
     *   Must be the same class
     *
     * @return boolean
     */
    public function equals($object)
    {
        return $object->type === $this->type && $object->id === $this->id;
    }
}
