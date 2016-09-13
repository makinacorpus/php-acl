<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\Entry;
use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\Cache\BitmaskEntry;
use MakinaCorpus\ACL\Cache\BitmaskMap;

/**
 * Entry list builder, this is what will be given to collector implementations
 * which ships a comprehensive and fluent domain language for users.
 */
final class EntryListBuilder
{
    private $resource;
    private $entries = [];

    /**
     * Default constructor
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
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
     * Convert this object as entry list
     */
    public function convertToEntryList()
    {
        $entries = [];
        //$map = new BitmaskMap();

        foreach ($this->entries as $type => $list) {
            foreach ($list as $id => $permissions) {
                // Here the original profile object is unnecessary, since it is
                // only meant to deal with ACL storage in the end
                $profile = new Profile($type, $id);
                $entries[] = new Entry($profile, array_keys($permissions));
                // $entries[] = new BitmaskEntry($profile, array_keys($permissions), $map);
            }
        }

        return new EntryList($this->resource, $entries);
    }

    /**
     * Add entry
     *
     * @param string $type
     *   Profile type
     * @param int|string $id
     *   Profile identifier
     * @param string|string[] $permission
     *   Single permission or permission array
     */
    public function add($type, $id, $permission)
    {
        // @todo should we fail on overwrite?
        if (is_array($permission)) {
            foreach ($permission as $real) {
                $this->entries[$type][(string)$id][$real] = true;
            }
        } else {
            $this->entries[$type][(string)$id][$permission] = true;
        }
    }

    /**
     * Relocate all permissions for a certain profile type to another
     *
     * @param string $previous
     *   Profile type to remove
     * @param string $next
     *   Profile type that inherits from
     * @param boolean $merge
     *   If target already exists, and this is set to false, existing
     *   permissions will be dropped before copy
     */
    public function relocateType($previous, $next, $merge = true)
    {
        if (!isset($this->entries[$previous])) {
            return; // @todo should it really be silent
        }

        if (isset($this->entries[$next])) {
            if (!$merge) {
                unset($this->entries[$next]);
            }
        }

        foreach ($this->entries[$previous] as $id => $permissions) {
            foreach ($permissions as $permission => $value) {
                $this->entries[$next][$id][$permission] = true;
            }
        }
    }

    /**
     * Relocate all permissions for a certain profile
     *
     * @param string $previousType
     *   Profile type to remove
     * @param int|string $previousId
     *   Profile identifier to remove
     * @param string $nextType
     *   Profile type that inherits from
     * @param int|string $nextId
     *   Profile identifier that inherits from
     * @param boolean $merge
     *   If target already exists, and this is set to false, existing
     *   permissions will be dropped before copy
     */
    public function relocate($previousType, $previousId, $nextType, $nextId, $merge = true)
    {
        $previousId = (string)$previousId;
        $nextId = (string)$nextId;

        if (!isset($this->entries[$previousType][$previousId])) {
            return; // @todo should it really be silent
        }

        if (isset($this->entries[$nextType][$nextId])) {
            if (!$merge) {
                unset($this->entries[$nextType][$nextId]);
            }
        }

        foreach ($this->entries[$previousType][$previousId] as $permission => $value) {
            $this->entries[$nextType][$nextId][$permission] = true;
        }
    }

    /**
     * Has permission for
     *
     * @param string $type
     *   Profile type
     * @param int|string $id
     *   Profile identifier
     * @param string $permission
     *   Single permission
     *
     * @return boolean
     */
    public function has($type, $id = null, $permission = null)
    {
        if (null === $id) {
            return isset($this->entries[$type]);
        } else if (null === $permission) {
            return isset($this->entries[$type][(string)$id]);
        } else {
            return isset($this->entries[$type][(string)$id][$permission]);
        }
    }

    /**
     * Remove a single or set of permissions
     *
     * @param string $type
     *   Profile type
     * @param int|string $id
     *   Profile identifier, if null is given remove all permissions for
     *   the given profile type
     * @param string $permission
     *   Single permission or permission array, if null given remove all
     *   permissions for the given profile
     *
     * @return boolean
     */
    public function remove($type, $id = null, $permission = null)
    {
        if (null === $id) {
            unset($this->entries[$type]);
        } else if (null === $permission) {
            unset($this->entries[$type][(string)$id]);
        } else {
            unset($this->entries[$type][(string)$id][$permission]);
        }
    }
}
