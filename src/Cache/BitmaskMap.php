<?php

namespace MakinaCorpus\ACL\Cache;

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
            if (!isset($this->map[$permission])) {
                throw new \InvalidArgumentException(sprintf("permission '%s' cannot be masked", $permission));
            }

            $value += $this->map[$permission];
        }

        return $value;
    }

    /**
     * Check if given value carries mask
     *
     * @param int $value
     *   Reference value being stored
     * @param string $permission
     *   Permission to check for
     */
    public function check($value, $permission)
    {
        if (isset($this->map[$permission])) {
            return (int)$value & $this->map[$permission];
        }

        return false;
    }
}
