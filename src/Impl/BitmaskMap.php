<?php

namespace MakinaCorpus\ACL\Impl;

use MakinaCorpus\ACL\Permission;

/**
 * For this to work, there is no magic in the air, you need to provide the
 * string to bitmask map by yourselves.
 *
 * We can help you generate one, and collect various calls to this API.
 */
class BitmaskMap
{
    private $map = [];

    /**
     * Default constructor
     *
     * @param int[] $map
     *   Keys are permission names, values are bitmasks
     */
    public function __construct(array $map = [])
    {
        if (!$map) {
            // Provide sensible defaults
            $map = [
                Permission::COMMENT => 1,
                Permission::DELETE => 2,
                Permission::HIDE => 4,
                Permission::LOCK => 8,
                Permission::MOVE => 16,
                Permission::SHARE => 32,
                Permission::SHOW => 64,
                Permission::TOUCH => 128,
                Permission::UNLOCK => 256,
                Permission::UPDATE => 512,
                Permission::VIEW => 1024,
            ];
        }

        $this->map = $map;
    }

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
