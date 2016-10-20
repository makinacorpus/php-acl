<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\PermissionMap;

/**
 * For this to work, there is no magic in the air, you need to provide the
 * string to bitmask map by yourselves.
 *
 * We can help you generate one, and collect various calls to this API.
 */
class BitmaskMap extends PermissionMap
{
    /**
     * Build bitmask value from permission strings
     *
     * @param string $permissions
     */
    public function build($permissions)
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
    public function getBitMap()
    {
        return $this->map;
    }

    /**
     * Get mask for the given permission
     *
     * @param string $permission
     *
     * @return int
     */
    public function getBit($permission)
    {
        if (isset($this->map[$permission])) {
            return $this->map[$permission];
        }

        return 0;
    }
}
