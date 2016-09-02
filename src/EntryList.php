<?php

namespace MakinaCorpus\ACL;

/**
 * Represent a full ACL for a single resource
 */
final class EntryList
{
    private $resource;
    private $entries = [];

    /**
     * Default constructor
     *
     * @param Resource $resource
     * @param Entry[] $entries
     */
    public function __construct(Resource $resource, array $entries)
    {
        $this->resource = $resource;
        $this->entries = $entries;
    }

    /**
     * Get resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get all entries
     *
     * @return Entry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Has the given permission
     *
     * @param string $permission
     *
     * @return boolean
     */
    public function hasPermissionFor(Profile $profile, $permission)
    {
        foreach ($this->entries as $entry) {
            if ($entry->getProfile()->equals($profile)) {
                if ($entry->hasPermission($permission)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is this instance empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        foreach ($this->entries as $entry) {
            if (!$entry->isEmpty()) {
                return false;
            }
        }

        return true;
    }
}
