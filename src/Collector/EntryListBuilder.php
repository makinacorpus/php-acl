<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\EntryList;
use MakinaCorpus\ACL\Identity;
use MakinaCorpus\ACL\PermissionMap;
use MakinaCorpus\ACL\Resource;

/**
 * Default entry list builder implementation
 */
final class EntryListBuilder
{
    private $entries = [];
    private $map;
    private $object;
    private $resource;

    /**
     * Default constructor
     *
     * @param Resource $resource
     * @param mixed $object
     * @param PermissionMap $map
     */
    public function __construct(Resource $resource, $object, PermissionMap $map = null)
    {
        $this->resource = $resource;
        $this->object = $object;

        if (!$map) {
            $map = new PermissionMap();
        }
        $this->map = $map;
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
     * Get resource
     */
    public function getResource() : Resource
    {
        return $this->resource;
    }

    /**
     * Add entry
     *
     * @param string $type
     *   Profile type
     * @param string $id
     *   Profile identifier
     * @param string|string[] $permission
     *   Single permission or permission array
     */
    public function add(string $type, string $id, $permission)
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
     * @param bool $merge
     *   If target already exists, and this is set to false, existing
     *   permissions will be dropped before copy
     */
    public function relocateType(string $previous, string $next, bool $merge = true)
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
     * Relocate all permissions for a certain profile to another
     *
     * @param string $previousType
     *   Profile type to remove
     * @param string $previousId
     *   Profile identifier to remove
     * @param string $nextType
     *   Profile type that inherits from
     * @param string $nextId
     *   Profile identifier that inherits from
     * @param bool $merge
     *   If target already exists, and this is set to false, existing
     *   permissions will be dropped before copy
     */
    public function relocate(string $previousType, string $previousId, string $nextType, string $nextId, bool $merge = true)
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
     * Alter group identifiers of existing grants
     *
     * For the given realm(s) change the associated given gid to the new
     * one instead, without changing anything else.
     *
     * @param string $oldProfileId
     *   Profile identifier to look for
     * @param string $newProfileId
     *   Profile identifier to replace the old one with
     * @param string|string[] $type
     *   Profile types to work with, if none given, replace for all
     */
    public function relocateProfile(string $oldProfileId, string $newProfileId, $type = null)
    {
        if (!$type) {
            $typeList = array_keys($this->entries);
        } else if (!is_array($type)) {
            $typeList = [$type];
        } else {
            $typeList = $type;
        }

        $oldProfileId = (string)$oldProfileId;
        $newProfileId = (string)$newProfileId;

        foreach ($typeList as $altered) {
            if (isset($this->entries[$altered][$oldProfileId])) {
                $this->entries[$altered][$newProfileId] = $this->entries[$altered][$oldProfileId];
                unset($this->entries[$altered][$oldProfileId]);
            }
        }
    }

    /**
     * Has permission for
     *
     * @param string $type
     *   Profile type
     * @param string $id
     *   Profile identifier
     * @param string $permission
     *   Single permission
     */
    public function has(string $type, string $id = null, string $permission = null) : bool
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
     * @param string $id
     *   Profile identifier, if null is given remove all permissions for
     *   the given profile type
     * @param string $permission
     *   Single permission or permission array, if null given remove all
     *   permissions for the given profile
     */
    public function remove(string $type, string $id = null, string $permission = null)
    {
        if (null === $id) {
            unset($this->entries[$type]);
        } else if (null === $permission) {
            unset($this->entries[$type][(string)$id]);
        } else {
            unset($this->entries[$type][(string)$id][$permission]);
        }
    }

    /**
     * Convert this object as entry list
     */
    public function convertToEntryList() : EntryList
    {
        $masks = [];

        foreach ($this->entries as $type => $list) {
            foreach ($list as $id => $permissions) {
                $strings = array_keys(array_filter($permissions));
                $masks[Identity::getStringRepresentation($type, $id)] = $this->map->build($strings);
            }
        }

        return new EntryList($this->map, $masks);
    }
}
