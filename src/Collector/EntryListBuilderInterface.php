<?php

namespace MakinaCorpus\ACL\Collector;

use MakinaCorpus\ACL\Profile;
use MakinaCorpus\ACL\Resource;
use MakinaCorpus\ACL\EntryListInterface;

/**
 * Builds entry lists
 */
interface EntryListBuilderInterface
{
    /**
     * Get resource
     *
     * @return Resource
     */
    public function getResource();

    /**
     * Get the original object being checked
     *
     * @return mixed
     */
    public function getObject();

    /**
     * Convert this object as entry list
     *
     * @return EntryListInterface
     */
    public function convertToEntryList();

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
    public function add($type, $id, $permission);

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
    public function relocateType($previous, $next, $merge = true);

    /**
     * Relocate all permissions for a certain profile to another
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
    public function relocate($previousType, $previousId, $nextType, $nextId, $merge = true);

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
    public function has($type, $id = null, $permission = null);

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
    public function remove($type, $id = null, $permission = null);
}
