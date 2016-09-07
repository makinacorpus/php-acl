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
    private $object;

    /**
     * Default constructor
     *
     * @param string $type
     * @param int|string $id
     * @param mixed $object
     */
    public function __construct($type, $id, $object = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->object = $object;
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
     * Has this instance an object
     *
     * @return boolean
     */
    public function hasObject()
    {
        return null !== $this->object;
    }

    /**
     * Get object
     *
     * @return mixed
     */
    public function getObject()
    {
        if (null === $this->object) {
            throw new \LogicException("does not carry an object");
        }

        return $this->object;
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
        return $object === $this || ($this->object && $object->object === $this->object) || ($object->type === $this->type && $object->id === $this->id);
    }
}
