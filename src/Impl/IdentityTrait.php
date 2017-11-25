<?php

namespace MakinaCorpus\ACL\Impl;

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
     */
    public function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = (string)$id;
    }

    /**
     * Get object type
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get object identifier
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Is this object the same
     *
     * @param mixed $object
     *   Must be the same class
     *
     * @return bool
     */
    public function equals($object) : bool
    {
        if (!is_object($object) || get_class($object) !== get_class($this)) {
            throw new \BadMethodCallException();
        }

        return $object->type === $this->type && $object->id === $this->id;
    }
}
