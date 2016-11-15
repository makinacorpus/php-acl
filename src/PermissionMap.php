<?php

namespace MakinaCorpus\ACL;

use MakinaCorpus\ACL\Permission;
use MakinaCorpus\ACL\Impl\NaiveEntryListBuilder;
use MakinaCorpus\ACL\Collector\EntryListBuilderInterface;

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

    public function addPermissions(array $permissions)
    {
        foreach ($permissions as $string => $mask) {
            if (isset($this->map[$string]) || in_array($mask, $this->map)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        "permission %s with mask %d overrides an existing permission",
                        $string, $mask
                    )
                );
            }

            $this->map[$string] = $mask;
        }
    }

    /**
     * Create entry list builder
     *
     * @param Resource $resource
     * @param mixed $object
     *
     * @return EntryListBuilderInterface
     */
    public function createEntryListBuilder(Resource $resource, $object)
    {
        return new NaiveEntryListBuilder($resource, $object);
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
