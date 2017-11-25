<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Collector\EntryListBuilder;

/**
 * Map of supported permissions, permissions will be identified by bit flags
 * for performance purposes, implementors that wishes to add additional
 * permissions for business purpose should be aware of this.
 *
 * Because bitmasks are int32 or int64 depending upon for which architecture
 * PHP was compiled for, try to stay under the 32 permissions count.
 */
class PermissionMap
{
    protected $map = [];

    /**
     * Default constructor
     *
     * @param int[] $map
     *   Keys are permission names, values are bit flags
     */
    public function __construct(array $map = [])
    {
        if (!$map) {
            // Provide sensible defaults
            $map = [
                Permission::CLONE => 1,
                Permission::COMMENT => 2,
                Permission::CREATE => 4,
                Permission::DELETE => 8,
                Permission::HIDE => 16,
                Permission::LOCK => 32,
                Permission::MOVE => 64,
                Permission::OVERVIEW => 128,
                Permission::PUBLISH => 256,
                Permission::SHARE => 512,
                Permission::SHOW => 1024,
                Permission::TOUCH => 2048,
                Permission::UNLOCK => 4096,
                Permission::UPDATE => 8192,
                Permission::VIEW => 16384,
            ];
        }

        $this->map = $map;
    }

    /**
     * Build bitmask value from permission strings
     *
     * @param string[] $permissions
     *
     * @return int
     *   Bitmask
     */
    public function build(array $permissions) : int
    {
        $value = 0;

        foreach ($permissions as $permission) {
            $value += $this->getBit($permission);
        }

        return $value;
    }

    /**
     * Get original permission to bit map
     *
     * @return int[]
     *   Keys are permissions, values are bit values
     */
    public function getBitMap() : array
    {
        return $this->map;
    }

    /**
     * Get single bit flag for the given permission
     */
    public function getBit(string $permission) : int
    {
        if (isset($this->map[$permission])) {
            return $this->map[$permission];
        }

        return 0;
    }

    /**
     * Add new permissions to this map
     *
     * @param int[] $permissions
     *   Keys are permissions strings, values are associated bit flags which
     *   must not exist in the current map
     *
     * @throws \InvalidArgumentException
     *   Whenever a bit flag overrides an existing one
     */
    public function addPermissions(array $permissions)
    {
        foreach ($permissions as $string => $mask) {
            if (isset($this->map[$string]) || in_array($mask, $this->map)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "permission '%s' with mask %d overrides the existing permission '%s'",
                        $string,
                        $mask,
                        array_search($mask, $this->map)
                    )
                );
            }

            $this->map[$string] = $mask;
        }
    }

    /**
     * Does this map supports the given permission
     */
    public function supports(string $permission) : bool
    {
        return isset($this->map[$permission]);
    }
}
