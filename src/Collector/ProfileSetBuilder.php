<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\ProfileSet;

/**
 * Profile set builder, this is what will be given to collector implementations
 * which ships a comprehensive and fluent domain language for users.
 */
final class ProfileSetBuilder
{
    private $object;
    private $entries = [];

    /**
     * Default constructor
     *
     * @param mixed $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Get original object if any
     *
     * @return null|mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Convert this object as entry list
     */
    public function convertToProfileSet() : ProfileSet
    {
        return ProfileSet::createFromArray($this->entries);
    }

    /**
     * Add profile
     *
     * @param string $type
     *   Profile type
     * @param string|string[] $id
     *   Profile identifier
     */
    public function add(string $type, $id)
    {
        // @todo should we fail on overwrite?
        if (is_array($id)) {
            foreach ($id as $real) {
                $this->entries[$type][(string)$real] = $real;
            }
        } else {
            $this->entries[$type][(string)$id] = $id;
        }
    }

    /**
     * Has profile
     *
     * @param string $type
     *   Profile type
     * @param string $id
     *   Profile identifier
     * @param string $permission
     *   Single permission
     *
     * @return bool
     */
    public function has(string $type, string $id = null) : bool
    {
        if (null === $id) {
            return isset($this->entries[$type]);
        } else {
            return isset($this->entries[$type][(string)$id]);
        }
    }

    /**
     * Remove a single or set of profiles
     *
     * @param string $type
     *   Profile type
     * @param string|string[] $id
     *   Profile identifier, if null is given remove all permissions for
     *   the given profile type
     *
     * @return bool
     */
    public function remove(string $type, string $id = null) : bool
    {
        if (null === $id) {
            unset($this->entries[$type]);
        } else {
            unset($this->entries[$type][(string)$id]);
        }
    }
}
