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
     *
     * @param string $type
     * @param string $id
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
     * @return string
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
        if (is_numeric($this->id)) {
            // Most frameworks out there actually won't cast integers coming
            // from their database as int and keep them as strings, we must
            // prevent any false negative due to poorly coded frameworks.
            return $object->type === $this->type && $object->id == $this->id;
        } else {
            return $object->type === $this->type && $object->id === $this->id;
        }
    }
}
