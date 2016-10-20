<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Permission;

/**
 * Map of supported permissions
 */
class PermissionMap
{
    protected $map = [];

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
     * Does this map supports the given permission
     *
     * @param string $permission
     *
     * @return boolean
     */
    public function supports($permission)
    {
        return isset($this->map[$permission]);
    }
}
